import crypto from 'crypto';

const SECRET_KEY = process.env.ENCRYPTION_KEY;
if (!SECRET_KEY) {
  throw new Error('ENCRYPTION_KEY environment variable is required');
}
const KEY_HASH = crypto.createHash('sha256').update(SECRET_KEY).digest();

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  if (!req.body) {
    return res.status(400).json({ error: 'Request body is required' });
  }
  const { scriptId, data, iv, mac } = req.body;

  if (!data || !iv || !mac) {
    return res.status(400).json({ error: 'Missing required fields' });
  }

  const ciphertext = Buffer.from(data, 'base64');
  const ivBuffer = Buffer.from(iv, 'hex');
  const macBuffer = Buffer.from(mac, 'hex');

  const expectedMac = crypto.createHmac('sha256', KEY_HASH)
    .update(ciphertext)
    .update(ivBuffer)
    .update(Buffer.from(scriptId || '', 'utf8'))
    .digest();

  if (!crypto.timingSafeEqual(macBuffer, expectedMac)) {
    return res.status(403).json({ error: 'MAC verification failed' });
  }

  try {
    const decipher = crypto.createDecipheriv('aes-256-cbc', KEY_HASH, ivBuffer);
    decipher.setAutoPadding(true);
    let plaintext = decipher.update(ciphertext, null, 'utf8');
    plaintext += decipher.final('utf8');

    return res.status(200).json({ code: plaintext });
  } catch (err) {
    return res.status(500).json({ error: 'Decryption failed' });
  }
}
