<!-- HEADER START -->
<header>
    <div class="header_menu container">
        <div class="logo">
            <a href="./"><img src="assets/media/images/logo/logo.svg" alt=""></a>
        </div>
        <nav id="main-nav">
            <a href="#about">О НАС</a>
            <a href="#masterclasses">МАСТЕРКЛАССЫ</a>
            <a href="#catalog">КУПИТЬ ИЗДЕЛИЕ</a>
            <a href="#studio">ЗАКАЗАТЬ ИЗДЕЛИЕ</a>
            <a href="#faq">FAQ</a>
        </nav>
        <div class="icons_in_header">
            <img src="assets/media/images/index/icon1.svg" alt="">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="./?page=profile"><img src="assets/media/images/index/profile.svg" alt=""></a>
            <?php else: ?>
                <a href="./?page=register"><img src="assets/media/images/index/profile.svg" alt=""></a>
            <?php endif; ?>
            <img src="assets/media/images/index/basket.svg" alt="">
            <button class="burger" aria-label="Открыть меню" aria-expanded="false" aria-controls="main-nav">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const burger = document.querySelector('.burger');
        const nav = document.getElementById('main-nav');
        if (!burger || !nav) return;
        const toggle = () => {
          const isOpen = burger.getAttribute('aria-expanded') === 'true';
          burger.setAttribute('aria-expanded', String(!isOpen));
          nav.classList.toggle('open');
          document.body.classList.toggle('nav-open', !isOpen);
        };
        burger.addEventListener('click', toggle);
        nav.addEventListener('click', (e) => {
          const target = e.target;
          if (target.tagName === 'A') {
            nav.classList.remove('open');
            burger.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('nav-open');
          }
        });
        // Smooth scroll for internal anchors
        document.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (id.length > 1) {
              const el = document.querySelector(id);
              if (el) {
                e.preventDefault();
                const headerOffset = 80;
                const top = el.getBoundingClientRect().top + window.scrollY - headerOffset;
                window.scrollTo({ top, behavior: 'smooth' });
              }
            }
          });
        });
      });
    </script>
</header>
<!-- HEADER END -->