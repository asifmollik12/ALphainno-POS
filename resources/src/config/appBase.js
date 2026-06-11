function readStockyBase() {
  if (typeof window !== 'undefined' && window.__STOCKY_BASE__) {
    return String(window.__STOCKY_BASE__).replace(/\/$/, '');
  }

  return '';
}

/** Path prefix only, e.g. "/pos" — for Vue Router base and relative API paths. */
export function appBasePath() {
  const base = readStockyBase();
  if (!base) {
    return '';
  }

  if (base.startsWith('http://') || base.startsWith('https://')) {
    try {
      return new URL(base).pathname.replace(/\/$/, '') || '';
    } catch (e) {
      return '';
    }
  }

  return base.startsWith('/') ? base.replace(/\/$/, '') : `/${base.replace(/\/$/, '')}`;
}

/** Full URL for hard navigation (window.location). */
export function appBaseUrl(path = '') {
  const root = readStockyBase();
  const suffix = String(path || '').replace(/^\//, '');

  if (root) {
    if (suffix === '') {
      return root;
    }

    return `${root}/${suffix}`;
  }

  return suffix === '' ? '/' : `/${suffix}`;
}
