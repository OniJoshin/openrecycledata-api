const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const fs = require('fs');
const path = require('path');

puppeteer.use(StealthPlugin());

const loadCategories = (filePath) => {
  const fullPath = path.resolve(__dirname, filePath);
  if (!fs.existsSync(fullPath)) return [];
  return JSON.parse(fs.readFileSync(fullPath, 'utf-8'));
};

const ensureDir = (dirPath) => {
  if (!fs.existsSync(dirPath)) fs.mkdirSync(dirPath, { recursive: true });
};

(async () => {
  const phoneCategories = loadCategories('data/phone_categories.json');
  const tabletCategories = loadCategories('data/tablet_categories.json');
  const browser = await puppeteer.launch({ headless: 'new' });

  const scrapeCategory = async (deviceType, category) => {
    const url = `https://www.compareandrecycle.co.uk/${deviceType}/${category}`;
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114 Safari/537.36');

    console.log(`📦 Scraping ${deviceType}/${category}...`);

    try {
      await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });

      const groupedOffers = {};

      const variantLinks = await page.$$eval('a[href*="?capacity="]', links =>
        [...new Set(links.map(a => a.href))]
      );

      for (const variantUrl of variantLinks) {
        console.log(`🔍 Variant: ${variantUrl}`);
        await page.goto(variantUrl, { waitUntil: 'networkidle0', timeout: 60000 });

        const offers = await page.$$eval('.comparison-price-info .grid', rows => {
          return Array.from(rows).map(row => {
            const merchantImg = row.querySelector('img[alt]')?.alt || 'Unknown';
            const priceText = row.querySelector('.font-extrabold')?.textContent || '£0';
            const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
            return {
              merchant: merchantImg,
              price,
              condition: 'Good',
              network: 'Unlocked',
            };
          });
        });

        const modelTitle = await page.title();
        const model = modelTitle.match(/Apple (.*?)(?: \d+GB)?\s*[-–]/)?.[1]?.trim() || 'Unknown';
        const storage = modelTitle.match(/(\d+GB)/)?.[1] || 'Unknown';
        const slug = variantUrl.split('/').pop()?.split('?')[0] || 'unknown';

        const key = `${deviceType}/${slug}`;
        groupedOffers[key] = {
          brand: 'Apple',
          model,
          storage,
          slug,
          source_slug: slug,
          type: deviceType === 'phones' ? 'phone' : 'tablet',
          offers: offers.map(o => ({
            ...o,
            source: 'compareandrecycle',
            timestamp: new Date().toISOString()
          }))
        };
      }

      const savePath = path.join('data/compareandrecycle', deviceType, `${category}.json`);
      ensureDir(path.dirname(savePath));
      fs.writeFileSync(savePath, JSON.stringify(groupedOffers, null, 2));
      console.log(`💾 Saved ${Object.keys(groupedOffers).length} devices to ${savePath}`);

    } catch (err) {
      console.error(`❌ Failed to scrape category ${category}: ${err.message}`);
    } finally {
      await page.close();
    }
  };

  for (const category of phoneCategories) {
    await scrapeCategory('mobile-phones', category);
  }

  for (const category of tabletCategories) {
    await scrapeCategory('tablets', category);
  }

  await browser.close();
})();