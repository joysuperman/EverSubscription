import * as wpElement from '@wordpress/element';
import App from './App';
import './styles.css';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('eversubscription-admin-app');

  if (!el) return;

  if (typeof wpElement.createRoot === 'function') {
    wpElement.createRoot(el).render(<App />);
  } else {
    wpElement.render(<App />, el);
  }
});
