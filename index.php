<?php
/**
 * PÁGINA INICIAL - REALIZA MÓVEIS
 * Exibe produtos em destaque com imagens do Supabase
 */

require_once 'config.php';

$busca = trim($_GET['busca'] ?? '');

try {
  if ($busca == '') {
    // Busca 9 produtos dando preferência a promoções e destaques
    $stmt = $pdo->prepare(
      "SELECT * FROM produtos 
         WHERE status = 'ativo'
         ORDER BY em_promocao DESC, destaque DESC, desconto_percentual DESC, data_cadastro DESC
         LIMIT 9"
    );
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar todos os produtos com imagens decodificadas
    $produtos = formatar_produtos($produtos);
  } else {
    $stmt = $pdo->prepare("
        SELECT * FROM produtos 
        WHERE status = 'ativo' 
        AND unaccent(REPLACE(LOWER(nome), '-', ' '))
        ILIKE unaccent(REPLACE(LOWER(:busca), '-', ' '))
      ");
    $stmt->bindValue(':busca', "%$busca%");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  }
} catch (PDOException $e) {
  error_log("Erro ao buscar produtos: " . $e->getMessage());
  $produtos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Realiza Móveis</title>
  <link rel="icon" type="image/svg+xml" href="assets/imgs/logoModificada.svg">
  <link rel="stylesheet" href="assets/css/cardsPromo.css">
  <link rel="stylesheet" href="style.css">
  <script src="assets/js/cliqueCards.js" defer></script>
  <script src="assets/js/mostrarCards.js" defer></script>

  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="styleClaude.css">
  <link rel="stylesheet" href="carrossel.css">
  <link rel="stylesheet" href="menuLateral.css">
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>

</head>

<body>
  <button class="menu-button" id="menuToggleBtn" aria-label="Abrir menu">
    <i class="fas fa-bars" style= "font-size: 20px;"></i>
  </button>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="top-bar-content">
      <div class="top-bar-item">
        <img src="assets/imgs/locBranco.svg" alt="Localização">
        <a href="#">Estrada do Cabuçu 3448, Rio de Janeiro</a>
      </div>
      <div class="top-bar-item">
        <i class="fas fa-phone"></i>
        <a href="tel:+5521979771368">(21) 97977-1368</a>
      </div>
    </div>
  </div>

  <!-- HEADER -->
  <header>
    <div class="header-container">
      <div class="header-logo">
        <img src="assets/imgs/LogoAchatada.svg" class="logo" alt="Logo Realiza Móveis">
      </div>
      <button class="cart-button" id="cartBtn" onclick="window.location.href='cart.html'">
        <span class="cart-button-icon">🛒 Ver Carrinho</span>
        <span class="cart-count" id="cartCount">0</span>
      </button>
    </div>
  </header>

  <!-- NAVIGATION -->
  <nav>
    <a href="index.php" class="nav-link active">
      <i class="fas fa-home"></i>
      <span>Início</span>
    </a>
    <a href="produtos.php" class="nav-link">
      <i class="fas fa-couch"></i>
      <span>Produtos</span>
    </a>
    <a href="https://wa.me/5521979771368" class="nav-link" target="_blank">
      <i class="fas fa-envelope"></i>
      <span>Contato</span>
    </a>
  </nav>

  <!-- CAROUSEL -->
  <div class="carousel" id="homepageCarousel" aria-label="Carrossel de banners">
    <div class="carousel-slides">
      <div class="carousel-slide mobile"><img src="assets/imgs/img1.jpeg"></div>
      <div class="carousel-slide mobile"><img src="assets/imgs/img2.jpeg"></div>
      <div class="carousel-slide mobile"><img src="assets/imgs/img3.jpeg"></div>

      <div class="carousel-slide desktop"><img src="assets/imgs/banner1.png"></div>
      <div class="carousel-slide desktop"><img src="assets/imgs/banner2.png"></div>
      <div class="carousel-slide desktop"><img src="assets/imgs/banner3.png"></div>
    </div>
    <div class="carousel-dots" id="carouselDots"></div>
  </div>

  <!-- PESQUISAR PRODUTOS -->
  <div class="pesquisar-produtos">
    <div class="pesquisar-produtos-header">
      <h2>Digite o nome do produto:</h2>
    </div>

    <form class="pesquisar-produtos-form" method="GET">
      <input type="text" id="inputBusca" name="busca" placeholder="Ex: Sofá, Mesa, Cadeira..." value="<?= htmlspecialchars($busca) ?>" required>

      <button type="submit">
        <i class="fas fa-search"></i>
      </button>

      <button type="button" onclick="limparBusca()" class="btn-limpar" style="background-color: red;">
        X
      </button>
    </form>
  </div>

  <div class="home-layout">
    <aside class="home-sidebar" id="homeSidebar">
      <div class="home-sidebar-panel">
        <div class="home-sidebar-header">
          <div>
            <h3 class="home-sidebar-title">Categorias</h3>
            <p class="home-sidebar-subtitle">Escolha o ambiente ideal para sua casa.</p>
          </div>
          <button type="button" class="sidebar-close-btn" aria-label="Fechar menu"><i class="fas fa-times"></i></button>
        </div>

        <nav class="home-sidebar-menu" aria-label="Menu lateral de categorias">
          <a class="sidebar-item" href="produtos.php?categoria=sofa">
            <span><i class="fas fa-couch"></i><span class="item-name">Sofás</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Sala%20de%20Estar">
            <span><i class="fas fa-tv"></i><span class="item-name">Sala de Estar</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Sala%20de%20Jantar">
            <span><i class="fas fa-utensils"></i><span class="item-name">Sala de Jantar</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Quarto">
            <span><i class="fas fa-bed"></i><span class="item-name">Quarto</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Cozinha">
            <span><i class="fas fa-bowl-food"></i><span class="item-name">Cozinha</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Office">
            <span><i class="fas fa-laptop"></i><span class="item-name">Office</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
          <a class="sidebar-item" href="produtos.php?categoria=Infantil">
            <span><i class="fas fa-baby"></i><span class="item-name">Infantil</span></span>
            <i class="fas fa-chevron-down icon-chevron"></i>
          </a>
        </nav>
      </div>
    </aside>

    <main class="home-main">

      <?php if (empty($produtos)): ?>
        <div class="sem-produtos">
          <h3>🔍 Nenhum produto encontrado</h3>
          <p>Tente ajustar os filtros de busca</p>
        </div>
      <?php else: ?>

        <div id="produtosArea" class="produtos-grid">
          <?php foreach ($produtos as $produto): ?>

            <div class="produto-card"
              onclick="window.location.href='produto-detalhes.php?id=<?php echo $produto['id']; ?>'">

              <div class="product-badge">
                <?php echo $produto['em_promocao'] ? 'Oferta' : htmlspecialchars($produto['categoria']); ?>
              </div>

              <div class="produto-imagem">
                <?php if (!empty($produto['primeira_imagem'])): ?>
                  <img src="<?php echo htmlspecialchars($produto['primeira_imagem']); ?>">
                <?php else: ?>
                  <div style="color:#ccc;display:flex;align-items:center;justify-content:center;height:100%;">
                    Sem imagem
                  </div>
                <?php endif; ?>
              </div>

              <div class="produto-conteudo">
                <span class="product-category"><?php echo htmlspecialchars($produto['marca']); ?></span>

                <h3 class="produto-titulo">
                  <?php echo htmlspecialchars($produto['nome']); ?>
                </h3>

                <p class="produto-descricao">
                  <?php echo mb_strimwidth(htmlspecialchars($produto['descricao']), 0, 100, "..."); ?>
                </p>

                <div class="produto-preco-container">
                  <?php if ($produto['em_promocao']): ?>
                    <span class="preco-atual">
                      R$ <?php echo formatar_preco($produto['preco_promocional']); ?>
                    </span>
                    <span class="preco-original-riscado">
                      R$ <?php echo formatar_preco($produto['preco']); ?>
                    </span>
                  <?php else: ?>
                    <span class="preco-atual">
                      R$ <?php echo formatar_preco($produto['preco']); ?>
                    </span>
                  <?php endif; ?>
                </div>

                <button class="btn-comprar"
                  onclick="event.stopPropagation(); window.location.href='produto-detalhes.php?id=<?php echo $produto['id']; ?>'">
                  VER DETALHES
                </button>
              </div>

            </div>

          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </main>
  </div>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- SESSÕES DE CATEGORIAS -->


  <style>

  </style>


  <!-- ━━━━━━━ 1. HERO REFINADO ━━━━━━━ -->
  <section class="hero-refined rm-section">
    <div class="hero-refined__bg"></div>
    <div class="hero-refined__bar"></div>
    <div class="hero-refined__content">
      <span class="hero-refined__tag" data-aos="fade-right">Nova Coleção 2026</span>
      <h1 class="hero-refined__title" data-aos="fade-up" data-aos-delay="100">
        Seu lar,
        <em>redesenhado.</em>
      </h1>
      <p class="hero-refined__sub" data-aos="fade-up" data-aos-delay="200">
        Móveis que combinam design inteligente com o conforto que sua família merece.
        Qualidade que você sente desde o primeiro olhar.
      </p>
      <div class="hero-refined__ctas" data-aos="fade-up" data-aos-delay="300">
        <a href="produtos.php" class="rm-btn filled">Explorar Coleção</a>
        <a href="#shop-by-room" class="rm-btn">Por Ambiente</a>
      </div>

    </div>
  </section>


  <!-- ━━━━━━━ 2. SHOP BY ROOM ━━━━━━━ -->
  <section class="room-section rm-section" id="shop-by-room">
    <div class="room-section__head" data-aos="fade-up">
      <span class="rm-eyebrow">Inspire-se</span>
      <h2 class="rm-heading">Compre <span>por ambiente</span></h2>
      <div class="rm-divider"></div>
      <p style="color:var(--gray);font-size:.95rem;max-width:520px;margin:0 auto;">
        Cada espaço conta uma história. Encontre os móveis perfeitos para cada cômodo da sua casa.
      </p>
    </div>
    <div class="room-grid">
      <div class="room-card" data-aos="fade-right" id="sofas-e-mesas">
        <img class="room-card__img" src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80"
          alt="Sala de Estar" loading="lazy">
        <div class="room-card__overlay"></div>
        <div class="room-card__text">
          <span class="room-card__label">Ambiente</span>
          <h3 class="room-card__title">Sala de Estar</h3>
          <a href="sofa.php" class="room-card__link">Ver Sofás & Mesas →</a>
        </div>
      </div>
      <div class="room-card" data-aos="fade-left" data-aos-delay="100" id="guarda-roupa">
        <img class="room-card__img" src="https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&q=80"
          alt="Quarto" loading="lazy">
        <div class="room-card__overlay"></div>
        <div class="room-card__text">
          <span class="room-card__label">Ambiente</span>
          <h3 class="room-card__title">Quarto</h3>
          <a href="guarda-roupa.php" class="room-card__link">Ver Guarda-Roupas →</a>
        </div>
      </div>
      <div class="room-card" data-aos="fade-left" data-aos-delay="200" id="escrivaninha">
        <img class="room-card__img" src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=800&q=80"
          alt="Escritório" loading="lazy">
        <div class="room-card__overlay"></div>
        <div class="room-card__text">
          <span class="room-card__label">Ambiente</span>
          <h3 class="room-card__title">Escritório</h3>
          <a href="escrivaninha.php" class="room-card__link">Ver Escrivaninhas →</a>
        </div>
      </div>
      <div class="room-card" data-aos="fade-up" data-aos-delay="300" id="mesa-de-jantar">
        <img class="room-card__img" src="https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80"
          alt="Área de Jantar" loading="lazy">
        <div class="room-card__overlay"></div>
        <div class="room-card__text">
          <span class="room-card__label">Ambiente</span>
          <h3 class="room-card__title">Área de Jantar</h3>
          <a href="mesa.php" class="room-card__link">Ver Mesas →</a>
        </div>
      </div>
    </div>
  </section>





  <!-- ━━━━━━━ 4. BANNER PROMOÇÃO ━━━━━━━ -->
  <section class="promo-banner rm-section">
    <div class="promo-banner__inner">
      <div class="promo-banner__left" data-aos="fade-right">
        <span class="promo-badge">Oferta por tempo limitado</span>
        <h2 class="promo-banner__title">Liquida <span>Realiza</span><br>só esta semana</h2>
        <p class="promo-banner__sub">Aproveite descontos exclusivos em sofás, guarda-roupas e estantes selecionados.
          Estoque limitado — garanta o seu agora.</p>
        <div class="countdown">
          <div class="cd-block"><span class="cd-num" id="cd-days">00</span><span class="cd-lbl">Dias</span></div>
          <div class="cd-block"><span class="cd-num" id="cd-hours">00</span><span class="cd-lbl">Horas</span></div>
          <div class="cd-block"><span class="cd-num" id="cd-min">00</span><span class="cd-lbl">Min</span></div>
          <div class="cd-block"><span class="cd-num" id="cd-sec">00</span><span class="cd-lbl">Seg</span></div>
        </div>
      </div>
      <div class="promo-banner__right" data-aos="fade-left">
        <span class="promo-discount">30%</span>
        <span class="promo-on">de desconto<br>em itens selecionados</span>
        <a href="produtos.php" class="rm-btn" style="margin-top:1.2rem;color:#fff;border-color:rgba(255,255,255,.4);"
          onmouseover="this.style.background='#D4AF37';this.style.color='#111';this.style.borderColor='#D4AF37'"
          onmouseout="this.style.background='transparent';this.style.color='#fff';this.style.borderColor='rgba(255,255,255,.4)'">
          Ver Ofertas
        </a>
      </div>
    </div>
  </section>



  <!-- ━━━━━━━ 6. FOOTER PREMIUM ━━━━━━━ -->
  <footer class="footer-premium">
    <div class="footer-premium__top">
      <div class="footer-brand">
        <div class="footer-brand__logo">
          <div class="footer-brand__ico">R</div>
          <div class="footer-brand__name"><strong>Realiza</strong><span>Móveis</span></div>
        </div>
        <p class="footer-brand__desc">Transformando casas em lares especiais com móveis de qualidade, preços justos e um
          atendimento que você não esquece.</p>
        <div class="footer-social">
          <a href="https://www.instagram.com/realizasonhomoveis" target="_blank" title="Instagram"><i
              class="fa-brands fa-instagram"></i></a>
          <a href="https://wa.me/5521979771368" target="_blank" title="WhatsApp"><i
              class="fa-brands fa-whatsapp"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Produtos</h4>
        <ul>
          <li><a href="sofa.php">Sofás</a></li>
          <li><a href="guarda-roupa.php">Guarda-Roupas</a></li>
          <li><a href="mesa.php">Mesas</a></li>
          <li><a href="armario.php">Armários</a></li>
          <li><a href="poltrona.php">Poltronas</a></li>
          <li><a href="rack-estantes.php">Racks & Estantes</a></li>
          <li><a href="cadeira.php">Cadeiras</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Ajuda</h4>
        <ul>
          <li><a href="#">Como comprar</a></li>
          <li><a href="#">Prazos de entrega</a></li>
          <li><a href="#">Trocas e devoluções</a></li>
          <li><a href="#">Política de privacidade</a></li>
          <li><a href="#">Termos de uso</a></li>
          <li><a href="#">Perguntas frequentes</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contato</h4>
        <div class="footer-contact-item"><i class="fa-solid fa-location-dot"></i><span>Estrada do Cabuçu, 3448 -
            RJ</span></div>
        <div class="footer-contact-item"><i class="fa-solid fa-phone"></i><span>(21) 97977-1368</span></div>
        <div style="margin-top:1rem;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,.1);">
          <iframe src="https://www.google.com/maps?q=Estrada+do+Cabu%C3%A7u,+3448,+Rio+de+Janeiro&output=embed"
            width="100%" height="130" style="border:0;display:block;filter:grayscale(1) invert(1) brightness(.7)"
            allowfullscreen="" loading="lazy">
          </iframe>
        </div>
      </div>
    </div>

    <div class="footer-premium__middle">
      <div class="footer-seals">
        <span class="footer-seal"><i class="fa-solid fa-lock"></i> Compra 100% segura</span>
        <span class="footer-seal"><i class="fa-solid fa-shield-halved"></i> Dados protegidos</span>
      </div>

    </div>

    <div class="footer-premium__bottom">
      <p class="footer-copyright">© 2026 Realiza Móveis. Todos os direitos reservados.</p>
    </div>
  </footer>


  <!-- ━━━━━━━ SCRIPTS ━━━━━━━ -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 60 });
      }
    });

    (function () {
      const target = new Date();
      const daysLeft = (7 - target.getDay()) % 7 || 7;
      target.setDate(target.getDate() + daysLeft);
      target.setHours(23, 59, 59, 0);
      const pad = n => String(n).padStart(2, '0');
      function tick() {
        const diff = target - Date.now();
        if (diff <= 0) { ['cd-days', 'cd-hours', 'cd-min', 'cd-sec'].forEach(id => document.getElementById(id).textContent = '00'); return; }
        document.getElementById('cd-days').textContent = pad(Math.floor(diff / 86400000));
        document.getElementById('cd-hours').textContent = pad(Math.floor((diff % 86400000) / 3600000));
        document.getElementById('cd-min').textContent = pad(Math.floor((diff % 3600000) / 60000));
        document.getElementById('cd-sec').textContent = pad(Math.floor((diff % 60000) / 1000));
      }
      tick(); setInterval(tick, 1000);
    })();
  </script>

  <!-- CARROSSEL SCRIPTS -->
  <script>
    (function () {
      const carousel = document.getElementById('homepageCarousel');
      if (!carousel) return;

      const slidesEl = carousel.querySelector('.carousel-slides');
      const dotsContainer = document.getElementById('carouselDots');
      if (!slidesEl) return;

      let current = 0;
      let slides = [];
      let total = 0;
      let timer;

      function atualizarSlides() {
        const isMobile = window.innerWidth <= 1024;

        const allSlides = Array.from(carousel.querySelectorAll('.carousel-slide'));

        slides = allSlides.filter(slide => {
          return isMobile
            ? slide.classList.contains('mobile')
            : slide.classList.contains('desktop');
        });

        total = slides.length;
        current = 0;

        // mostra/esconde slides
        allSlides.forEach(slide => {
          slide.style.display = slides.includes(slide) ? 'block' : 'none';
        });

        // recria dots
        dotsContainer.innerHTML = '';
        slides.forEach((_, i) => {
          const btn = document.createElement('button');
          if (i === 0) btn.classList.add('active');

          btn.addEventListener('click', () => {
            current = i;
            update();
            resetTimer();
          });

          dotsContainer.appendChild(btn);
        });

        update();
      }

      function update() {
        if (total === 0) return;

        slidesEl.style.transform = `translateX(-${current * 100}%)`;

        Array.from(dotsContainer.children).forEach((b, i) => {
          b.classList.toggle('active', i === current);
        });
      }

      function startTimer() {
        timer = setInterval(() => {
          current = (current + 1) % total;
          update();
        }, 4000);
      }

      function resetTimer() {
        clearInterval(timer);
        startTimer();
      }

      function fitImageToSlide(img) {
        const isMobile = window.innerWidth <= 1024;

        // 👉 NO MOBILE: deixa o CSS mandar (imagem full)
        if (isMobile) {
          img.style.width = '100%';
          img.style.height = '100%';
          img.style.objectFit = 'cover'; // ou 'contain' se quiser sem corte
          return;
        }

        // 👉 DESKTOP: mantém seu comportamento atual
        const slide = img.closest('.carousel-slide');
        if (!slide) return;

        const cW = slide.clientWidth;
        const cH = slide.clientHeight;
        const imgW = img.naturalWidth || img.width;
        const imgH = img.naturalHeight || img.height;

        if (!imgW || !imgH) return;

        const imgRatio = imgW / imgH;
        const containerRatio = cW / cH;

        if (imgRatio >= containerRatio) {
          img.style.width = '100%';
          img.style.height = 'auto';
        } else {
          img.style.width = 'auto';
          img.style.height = '100%';
        }
      }

      function adjustAllImages() {
        const imgs = carousel.querySelectorAll('.carousel-slide img');

        imgs.forEach(img => {
          if (img.complete) {
            fitImageToSlide(img);
          } else {
            img.addEventListener('load', () => fitImageToSlide(img), { once: true });
          }
        });
      }

      function debounce(fn, wait) {
        let t;
        return function (...args) {
          clearTimeout(t);
          t = setTimeout(() => fn.apply(this, args), wait);
        };
      }

      window.addEventListener('resize', debounce(() => {
        atualizarSlides();
        adjustAllImages();
      }, 200));

      atualizarSlides();
      adjustAllImages();
      setTimeout(adjustAllImages, 500);
      startTimer();

    })();
  </script>
  <!-- MENU LATERAL -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const menuToggleBtn = document.getElementById('menuToggleBtn');
    const homeSidebar = document.getElementById('homeSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');

    function openSidebar() {
      homeSidebar?.classList.add('open');
      sidebarOverlay?.classList.add('active');
      // Adiciona a classe para esconder o botão de abrir
      menuToggleBtn?.classList.add('hidden');
    }

    function closeSidebar() {
      homeSidebar?.classList.remove('open');
      sidebarOverlay?.classList.remove('active');
      // Remove a classe para o botão de abrir voltar
      menuToggleBtn?.classList.remove('hidden');
    }

    menuToggleBtn?.addEventListener('click', openSidebar);
    sidebarCloseBtn?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeSidebar();
      }
    });
  });
  </script>

  <!-- busca -->
  <script>
    function buscarProdutos(event) {
      event.preventDefault();

      const busca = document.getElementById("inputBusca").value;

      fetch(`buscar-produtos.php?busca=${encodeURIComponent(busca)}`)
        .then(res => res.json())
        .then(produtos => {
          renderizarProdutos(produtos);
        });
    }

    function limparBusca() {
      document.getElementById("inputBusca").value = "";

      fetch(`buscar-produtos.php`)
        .then(res => res.json())
        .then(produtos => {
          renderizarProdutos(produtos);
        });
    }

    function renderizarProdutos(produtos) {
      const area = document.getElementById("produtosArea");

      if (!produtos.length) {
        area.innerHTML = `
      <div class="sem-produtos">
        <h3>🔍 Nenhum produto encontrado</h3>
        <p>Tente ajustar a busca</p>
      </div>
    `;
        return;
      }

      area.innerHTML = produtos.map(produto => `
    <div class="produto-card" onclick="window.location.href='produto-detalhes.php?id=${produto.id}'">
      
      <div class="product-badge">
        ${produto.em_promocao ? 'Oferta' : produto.categoria}
      </div>

      <div class="produto-imagem">
        ${produto.primeira_imagem
          ? `<img src="${produto.primeira_imagem}" />`
          : `<div style="color:#ccc;display:flex;align-items:center;justify-content:center;height:100%;">Sem imagem</div>`
        }
      </div>

      <div class="produto-conteudo">
        <span class="product-category">${produto.marca}</span>
        <h3 class="produto-titulo">${produto.nome}</h3>
        <p class="produto-descricao">
          ${produto.descricao.substring(0, 100)}...
        </p>

        <div class="produto-preco-container">
          ${produto.em_promocao
          ? `
              <span class="preco-atual">R$ ${produto.preco_promocional}</span>
              <span class="preco-original-riscado">R$ ${produto.preco}</span>
            `
          : `
              <span class="preco-atual">R$ ${produto.preco}</span>
            `
        }
        </div>

        <button class="btn-comprar" onclick="event.stopPropagation(); window.location.href='produto-detalhes.php?id=${produto.id}'">
          VER DETALHES
        </button>
      </div>
    </div>
  `).join('');
    }
  </script>
  <script>
    function limparBusca() {
      window.location.href = window.location.pathname;
    }
  </script>
</body>

</html>