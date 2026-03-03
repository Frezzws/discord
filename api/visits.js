function getRedis() {
  const url = process.env.UPSTASH_REDIS_REST_URL || process.env.KV_REST_API_URL || process.env.STORAGE_REDIS_REST_URL || process.env.STORAGE_REDIS_URL || process.env.STORAGE_URL;
  const token = process.env.UPSTASH_REDIS_REST_TOKEN || process.env.KV_REST_API_TOKEN || process.env.STORAGE_REDIS_REST_TOKEN || process.env.STORAGE_REDIS_TOKEN || process.env.STORAGE_TOKEN;
  if (!url || !token) return null;
  try {
    const { Redis } = require('@upstash/redis');
    return new Redis({ url, token });
  } catch (_) { return null; }
}

module.exports = async (req, res) => {
  if (req.method !== 'GET') {
    res.status(405).json([]);
    return;
  }
  res.setHeader('Content-Type', 'application/json');
  try {
    const redis = getRedis();
    if (!redis) {
      res.status(200).json([]);
      return;
    }
    const raw = await redis.lrange('site-logs', 0, 499);
    const list = (raw || []).map(function (s) {
      try {
        const o = JSON.parse(s);
        return { ip: o.ip, user_agent: o.userAgent || o.user_agent, time: o.time };
      } catch (_) {
        return null;
      }
    }).filter(Boolean);
    res.status(200).json(list);
  } catch (_) {
    res.status(200).json([]);
  }
};
