export function appBasePath() {
  const base = (typeof window !== 'undefined' && window.__STOCKY_BASE__)
    ? String(window.__STOCKY_BASE__)
    : '';

  return base.replace(/\/$/, '');
}

export function appBaseUrl(path = '') {
  const base = appBasePath();
  const suffix = String(path || '');

  if (suffix === '') {
    return base || '/';
  }

  return `${base}/${suffix.replace(/^\//, '')}`;
}
