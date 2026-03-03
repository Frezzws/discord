function getRedis() {
  const url = process.env.UPSTASH_REDIS_REST_URL || process.env.KV_REST_API_URL;
  const token = process.env.UPSTASH_REDIS_REST_TOKEN || process.env.KV_REST_API_TOKEN;
  if (!url || !token) return null;
  try {
    const { Redis } = require('@upstash/redis');
    return new Redis({ url, token });
  } catch (_) { return null; }
}

function parseUA(ua) {
  var browser = 'Bilinmiyor';
  if (/Chrome\/[0-9.]+/.test(ua) && !/Edg/.test(ua)) browser = 'Chrome';
  else if (/Firefox\/[0-9.]+/.test(ua)) browser = 'Firefox';
  else if (/Edg\/[0-9.]+/.test(ua)) browser = 'Edge';
  else if (/Safari\/[0-9.]+/.test(ua) && !/Chrome/.test(ua)) browser = 'Safari';
  else if (/OPR\/[0-9.]+/.test(ua)) browser = 'Opera';
  var os = 'Bilinmiyor';
  if (/Windows NT [0-9.]+/.test(ua)) os = 'Windows';
  else if (/Mac OS X/.test(ua)) os = 'macOS';
  else if (/Android [0-9.]+/.test(ua)) os = 'Android';
  else if (/iPhone|iPad/.test(ua)) os = 'iOS';
  else if (/Linux/.test(ua)) os = 'Linux';
  return { browser: browser, os: os };
}

module.exports = async (req, res) => {
  if (req.method !== 'GET') {
    res.status(405).json({});
    return;
  }
  var ip = (req.query && req.query.ip) ? String(req.query.ip).trim() : '';
  res.setHeader('Content-Type', 'application/json');
  if (!ip) {
    res.status(200).json({ ip: '', browser: '—', os: '—', konum: '—', ekran_karti: '—' });
    return;
  }
  try {
    var entry = null;
    var redis = getRedis();
    if (redis) {
      var raw = await redis.lrange('site-logs', 0, 499);
      for (var i = 0; i < (raw || []).length; i++) {
        try {
          var o = JSON.parse(raw[i]);
          if (o && o.ip === ip) { entry = o; break; }
        } catch (_) {}
      }
    }
    var ua = (entry && entry.userAgent) || (entry && entry.user_agent) || '';
    var parsed = parseUA(ua);
    res.status(200).json({
      ip: ip,
      browser: parsed.browser,
      os: parsed.os,
      konum: '—',
      ekran_karti: '—',
      user_agent: ua,
      time: entry && entry.time ? entry.time : ''
    });
  } catch (_) {
    res.status(200).json({ ip: ip, browser: '—', os: '—', konum: '—', ekran_karti: '—' });
  }
};
