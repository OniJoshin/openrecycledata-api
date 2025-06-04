const puppeteer = require('puppeteer-extra');
const stealth = require('puppeteer-extra-plugin-stealth');
const fs = require('fs');
const path = require('path');

puppeteer.use(stealth());

const loadCategories = (filePath) => {
  const fullPath = path.resolve(__dirname, filePath);
  if (!fs.existsSync(fullPath)) return [];
  return JSON.parse(fs.readFileSync(fullPath, 'utf-8'));
};

const ensureDir = (dirPath) => {
  if (!fs.existsSync(dirPath)) fs.mkdirSync(dirPath, { recursive: true });
};

const normalizeDevicePath = (href) => {
  const url = new URL(href, 'https://www.sellmymobile.com');
  const parts = url.pathname.split('/').filter(Boolean);

  const startIdx = parts.findIndex(p => p === 'phones' || p === 'tablets');
  if (startIdx === -1) return null;
  const relevant = parts.slice(startIdx);

  const blacklist = ['tesco', 'sky', 'giffgaff', 'tmobile', 'virgin', 'vodafone', 'three', 'o2', 'ee', 'orange', 'id', 'other', 'unlocked'];
  if (blacklist.includes(relevant.at(-1).toLowerCase())) {
    relevant.pop();
  }

  return '/' + relevant.join('/');
};

(async () => {
  const phoneCategories = loadCategories('data/phone_categories.json');
  const tabletCategories = loadCategories('data/tablet_categories.json');

  const browser = await puppeteer.launch({ headless: 'new' });

  const scrapeCategory = async (deviceType, category) => {
    const url = `https://www.sellmymobile.com/${deviceType}/${category}/`;
    const page = await browser.newPage();
    console.log(`📦 Scraping ${deviceType}/${category}...`);

    try {
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });

      const rawDeviceUrls = await page.evaluate(() =>
        Array.from(document.querySelectorAll('.device-to-results__name-desktop')).map(a => a.href)
      );

      const deviceUrls = [...new Set(rawDeviceUrls.map(normalizeDevicePath).filter(Boolean))];

      const groupedOffers = {};

      for (const devicePath of deviceUrls) {
        const fullUrl = 'https://www.sellmymobile.com' + devicePath;
        const tab = await browser.newPage();
        console.log(`🔍 Scraping: ${fullUrl}`);

        try {
          await tab.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 60000 });

          // Get all capacity variant links
          const variantHrefs = await tab.evaluate(() =>
            Array.from(document.querySelectorAll('.device-to-results__variant a.cta'))
              .map(a => a.href.split('?')[0])
          );

          const variantUrls = [...new Set(variantHrefs)];

          for (const variantUrl of variantUrls) {
            try {
              console.log(`📥 Variant: ${variantUrl}`);
              await tab.goto(variantUrl, { waitUntil: 'domcontentloaded', timeout: 60000 });

              const { offers, brand, slug } = await tab.evaluate(() => {
                const deals = window.serverViewModel?.deals || [];
                const brand = window.serverViewModel?.manufacturerName || 'Unknown';
                const slug = window.serverViewModel?.minorVariantSlug || 'unknown-variant';

                const parsed = deals.map(item => {
                  const storage = item.productSku.match(/\d+gb/i)?.[0]?.toUpperCase() || 'Unknown';
                  const network = item.network || 'Unknown';
                  const condition = item.condition || 'Unknown';
                  const price = item.quote || 0;
                  const merchant = item.provider?.name || 'Unknown';
                  const modelParts = item.productSku.split('-').slice(2, -3).join(' ').replace(/-/g, ' ').toUpperCase();

                  return {
                    brand,
                    model: modelParts,
                    storage,
                    network,
                    condition,
                    price: parseFloat(price),
                    merchant,
                  };
                });

                return { offers: parsed, brand, slug };
              });

              const key = `${deviceType}/${slug}`;

              if (!groupedOffers[key]) {
                const first = offers[0] || {};
                groupedOffers[key] = {
                  brand: first.brand || 'Unknown',
                  model: first.model || 'Unknown',
                  storage: first.storage || 'Unknown',
                  slug: slug.replace(/\//g, '-'),
                  source_slug: slug,
                  type: deviceType === 'phones' ? 'phone' : 'tablet',
                  offers: [],
                };
              }

              groupedOffers[key].offers.push(...offers.map(o => ({
                merchant: o.merchant,
                price: o.price,
                condition: o.condition,
                network: o.network,
                source: 'sellmymobile',
                timestamp: new Date().toISOString(),
              })));
            } catch (err) {
              console.error(`❌ Variant error: ${variantUrl} — ${err.message}`);
            }
          }

        } catch (err) {
          console.error(`❌ Error scraping ${fullUrl}: ${err.message}`);
        } finally {
          await tab.close();
        }
      }

      const savePath = path.join('data/sellmymobile', deviceType, `${category}.json`);
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
    await scrapeCategory('phones', category);
  }

  for (const category of tabletCategories) {
    await scrapeCategory('tablets', category);
  }

  await browser.close();
})();
