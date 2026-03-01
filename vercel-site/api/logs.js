function getRedis() {
  const url = process.env.UPSTASH_REDIS_REST_URL || process.env.KV_REST_API_URL || process.env.STORAGE_REDIS_REST_URL || process.env.STORAGE_REDIS_URL || process.env.STORAGE_URL;
  const token = process.env.UPSTASH_REDIS_REST_TOKEN || process.env.KV_REST_API_TOKEN || process.env.STORAGE_REDIS_REST_TOKEN || process.env.STORAGE_REDIS_TOKEN || process.env.STORAGE_TOKEN;
  if (!url || !token) return null;
  const { Redis } = require('@upstash/redis');
  return new Redis({ url, token });
}

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
    const redis = getRedis();
    if (redis) {
      const raw = await redis.lrange('site-logs', 0, -1);
      list = (raw || []).map((s) => {
        try {
          return typeof s === 'string' ? JSON.parse(s) : s;
        } catch {
          return null;
        }
      }).filter(Boolean);
      list.reverse();
    }
  } catch (_) {
    list = [];
  }
  res.setHeader('Content-Type', 'application/json');
  res.status(200).end(JSON.stringify(list));
};
