module.exports = async (req, res) => {
  if (req.method !== 'GET') {
    res.status(405).end();
    return;
  }
  const key = (req.query?.key || '').trim();
  const secret = process.env.LOG_SECRET || '';
  if (!secret || key !== secret) {
    res.setHeader('Content-Type', 'application/json');
    res.status(403).end(JSON.stringify({ error: 'Yetkisiz' }));
    return;
  }
  let list = [];
  try {
    const { kv } = require('@vercel/kv');
    const raw = await kv.lrange('site-logs', 0, -1);
    list = (raw || []).map((s) => {
      try {
        return typeof s === 'string' ? JSON.parse(s) : s;
      } catch {
        return null;
      }
    }).filter(Boolean);
    list.reverse();
  } catch (_) {
    list = [];
  }
  res.setHeader('Content-Type', 'application/json');
  res.status(200).end(JSON.stringify(list));
};
