const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

(async () => {
  const browser = await puppeteer.launch({ headless: 'new' });
  const page = await browser.newPage();

  const url = 'https://www.compareandrecycle.co.uk/mobile-phones/apple-iphone-13?capacity=256gb&network=unlocked&condition=working';
  await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114 Safari/537.36');

  console.log(`🔍 Visiting: ${url}`);
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });

  const offers = await page.$$eval('.comparison-price-info .grid', rows => {
    return Array.from(rows).map(row => {
      const merchantImg = row.querySelector('img[alt]')?.alt || 'Unknown';
      const priceText = row.querySelector('.font-extrabold')?.textContent || '£0';
      const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
      const condition = 'Good';
      const network = 'Unlocked';

      return {
        merchant: merchantImg,
        price,
        condition,
        network
      };
    });
  });

  const modelTitle = await page.title();
  const model = modelTitle.match(/Apple (.*?)(?: \d+GB)?\s*[-–]/)?.[1]?.trim() || 'Unknown';
  const storage = modelTitle.match(/(\d+GB)/)?.[1] || 'Unknown';
  const slug = url.split('/').pop()?.split('?')[0] || 'unknown';
  const brand = 'Apple';

  const result = {
    brand,
    model,
    storage,
    slug,
    offers
  };

  console.log('✅ Parsed Data:\n', JSON.stringify(result, null, 2));

  await browser.close();
})();
