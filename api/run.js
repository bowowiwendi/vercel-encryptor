module.exports = function handler(req, res) {
  res.status(200).json({ status: 'alive' });
};
