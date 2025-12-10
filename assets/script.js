// assets/script.js - small enhancement for theme toggle + simple animation (keeps original behavior)
(function(){
  const root = document.documentElement;
  const storageKey = 'site_theme';

  function setTheme(theme) {
    if (theme === 'dark') {
      root.setAttribute('data-theme','dark');
    } else {
      root.removeAttribute('data-theme');
    }
    localStorage.setItem(storageKey, theme);
  }

  const saved = localStorage.getItem(storageKey);
  const preferred = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  setTheme(saved || preferred);

  // simple reveal on scroll
  const observer = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        e.target.classList.add('in-view');
      }
    });
  }, {threshold: 0.12});
  document.querySelectorAll('.card, .news-item, .menu-vertical-item').forEach(el=>{
    observer.observe(el);
  });
})();