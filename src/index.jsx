import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import './styles.css';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('eversubscription-admin-app');

  if (!el) return;

  // Use React 18 createRoot if available, otherwise use render
  if (ReactDOM.createRoot) {
    const root = ReactDOM.createRoot(el);
    root.render(React.createElement(App));
  } else {
    ReactDOM.render(React.createElement(App), el);
  }
});
