module.exports = function(api) {
  api.cache(true);
  return {
    presets: [
      ['@babel/preset-env', { 
        targets: { browsers: 'defaults' },
        modules: true // Let webpack handle ES6 modules
      }],
      ['@babel/preset-react', { runtime: 'classic' }]
    ]
  };
};
