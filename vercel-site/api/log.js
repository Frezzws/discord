const getIp = (req) => {
  const x = req.headers['x-forwarded-for'] || req.headers['x-real-ip'];
  if (x) return (typeof x === 'string' ? x : x[0] || '').split(',')[0].trim();
  return req.socket?.remoteAddress || 'unknown';
};

function getRedis() {
  const url = process.env.UPSTASH_REDIS_REST_URL || process.env.KV_REST_API_URL;
  const token = process.env.UPSTASH_REDIS_REST_TOKEN || process.env.KV_REST_API_TOKEN;
  if (!url || !token) return null;
  const { Redis } = require('@upstash/redis');
  return new Redis({ url, token });
}

module.exports = async (req, res) => {
  if (req.method !== 'GET') {
    res.status(405).end();
    return;
  }
  const type = (req.query?.type || 'visit').toString().slice(0, 20);
  const ip = getIp(req);
  const userAgent = (req.headers['user-agent'] || '-').slice(0, 400);
  const entry = JSON.stringify({
    time: new Date().toISOString(),
    type: type === 'click' ? 'click' : 'visit',
    ip,
    userAgent,
    discord: '-'
  });
  try {
    const redis = getRedis();
    if (redis) await redis.lpush('site-logs', entry);
  } catch (_) {}
  res.setHeader('Content-Type', 'text/plain');
  res.status(200).end('ok');
};
