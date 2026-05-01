<?php
/**
 * PÁGINA INICIAL - REALIZA MÓVEIS
 * Exibe produtos em destaque com imagens do Supabase
 */

require_once 'config.php';

try {
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
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>

    <style>
        /* Carousel styles */
        html, body { margin: 0; padding: 0; }

        .carousel {
            width: 100%;
            max-width: 1774px;
            height: 263px;
            overflow: hidden;
            position: relative;
            border-radius: 0 0 12px 12px;
            box-shadow: none;
            margin: 0 auto 20px auto;
        }

        .carousel-slides {
            display: flex;
            transition: transform 0.6s ease;
            will-change: transform;
        }

        .carousel-slide {
            min-width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 263px;
            background: transparent;
        }

        .carousel-slide img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            display: block;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .carousel-controls button {
            pointer-events: all;
            background: rgba(0,0,0,0.45);
            color: white;
            border: none;
            padding: 10px 12px;
            margin: 0 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        .carousel-dots {
            position: absolute;
            left: 50%;
            bottom: 40px;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 5;
        }

        .carousel-dots button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.6);
            cursor: pointer;
            padding: 0;
        }

        .carousel-dots button.active {
            background: var(--gold);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        @media (min-width: 1025px) {
      .carousel-slide:nth-child(-n+3) {
        display: none;
        /* esconde img1, img2, img3 */
        }
        }

        /* Mobile (<=1024px) */
        @media (max-width: 1024px) {
        .carousel {
            height: auto;
        }

        .carousel-slide {
            width: 100%;
            height: auto;
        }

        .carousel-slide img {
            width: 100%;
            height: auto;
            display: block;
        }
        }
    </style>
</head>
<body>
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
                <div class="header-tagline">Móveis de Qualidade para sua Casa</div>
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

    <!-- PRODUTOS EM DESTAQUE -->
    <?php if (empty($produtos)): ?>
        <div class="sem-produtos">
            <h3>🔍 Nenhum produto encontrado</h3>
            <p>Tente ajustar os filtros de busca</p>
        </div>
    <?php else: ?>
        <div id="produtosArea" class="produtos-grid">
            <?php foreach ($produtos as $produto): ?>
                <div class="produto-card" onclick="window.location.href='produto-detalhes.php?id=<?php echo $produto['id']; ?>'">
                    <div class="product-badge">
                        <?php echo $produto['em_promocao'] ? 'Oferta' : htmlspecialchars($produto['categoria']); ?>
                    </div>

                    <div class="produto-imagem">
                        <?php if (!empty($produto['primeira_imagem'])): ?>
                            <img src="<?php echo htmlspecialchars($produto['primeira_imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2214%22 fill=%22%23ccc%22%3ESem imagem%3C/text%3E%3C/svg%3E'">
                        <?php else: ?>
                            <div style="color: #ccc; display: flex; align-items: center; justify-content: center; height: 100%;">
                                Sem imagem
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="produto-conteudo">
                        <span class="product-category"><?php echo htmlspecialchars($produto['marca']); ?></span>
                        <h3 class="produto-titulo"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="produto-descricao">
                            <?php echo mb_strimwidth(htmlspecialchars($produto['descricao']), 0, 100, "..."); ?>
                        </p>

                        <div class="produto-preco-container">
                            <?php if ($produto['em_promocao']): ?>
                                <span class="preco-atual">R$ <?php echo formatar_preco($produto['preco_promocional']); ?></span>
                                <span class="preco-original-riscado">R$ <?php echo formatar_preco($produto['preco']); ?></span>
                            <?php else: ?>
                                <span class="preco-atual">R$ <?php echo formatar_preco($produto['preco']); ?></span>
                            <?php endif; ?>
                        </div>

                        <button class="btn-comprar" onclick="event.stopPropagation(); window.location.href='produto-detalhes.php?id=<?php echo $produto['id']; ?>'">
                            VER DETALHES
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- SESSÕES DE CATEGORIAS -->
    

    <style>
  :root {
    --gold:    #D4AF37;
    --gold-lt: #e8cc6a;
    --gold-dk: #a8891d;
    --dark:    #1a1a1a;
    --gray:    #4a4a4a;
    --light:   #f8f6f1;
    --white:   #ffffff;
  }

  .rm-section { font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; }

  .rm-heading {
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 600; color: var(--dark);
    letter-spacing: -.5px; line-height: 1.2;
  }
  .rm-heading span { color: var(--gold); }

  .rm-eyebrow {
    font-size: .72rem; letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold); font-weight: 600; margin-bottom: .5rem; display: block;
  }

  .rm-divider {
    width: 48px; height: 2px; background: var(--gold);
    border-radius: 2px; margin: 1rem auto 1.5rem;
  }
  .rm-divider.left { margin-left: 0; }

  .rm-btn {
    display: inline-block; padding: .75rem 2rem;
    border: 2px solid var(--gold); color: var(--gold);
    font-size: .82rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; text-decoration: none;
    border-radius: 4px; transition: all .3s; cursor: pointer; background: transparent;
  }
  .rm-btn:hover, .rm-btn.filled { background: var(--gold); color: var(--dark); }
  .rm-btn.filled:hover { background: var(--gold-dk); border-color: var(--gold-dk); }

  /* ── HERO ── */
  .hero-refined {
    position: relative; min-height: 520px;
    background: var(--dark); overflow: hidden; display: flex; align-items: center;
  }
  .hero-refined__bg {
    position: absolute; inset: 0;
    background: url('https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1600&q=80') center/cover no-repeat;
    opacity: .38;
  }
  .hero-refined__bar { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--gold); }
  .hero-refined__content { position: relative; z-index: 2; max-width: 1280px; margin: 0 auto; padding: 80px 40px; width: 100%; }
  .hero-refined__tag {
    display: inline-block; background: var(--gold); color: var(--dark);
    font-size: .7rem; font-weight: 700; letter-spacing: 3px;
    text-transform: uppercase; padding: .4rem 1.2rem; border-radius: 2px; margin-bottom: 1.5rem;
  }
  .hero-refined__title {
    font-size: clamp(2.2rem, 5vw, 4rem); font-weight: 700; color: #fff;
    line-height: 1.1; margin-bottom: 1.2rem; max-width: 700px; letter-spacing: -1px;
  }
  .hero-refined__title em { font-style: normal; color: var(--gold); display: block; }
  .hero-refined__sub { font-size: 1.05rem; color: rgba(255,255,255,.75); max-width: 480px; line-height: 1.7; margin-bottom: 2.5rem; }
  .hero-refined__ctas { display: flex; gap: 1rem; flex-wrap: wrap; }
  .hero-refined__stats { display: flex; gap: 2.5rem; margin-top: 4rem; flex-wrap: wrap; }
  .hero-stat-num { font-size: 1.8rem; font-weight: 700; color: var(--gold); display: block; line-height: 1; }
  .hero-stat-lbl { font-size: .78rem; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: 1px; }

  /* ── ROOM ── */
  .room-section { background: var(--light); padding: 80px 40px; }
  .room-section__head { text-align: center; margin-bottom: 3rem; }
  .room-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; max-width: 1200px; margin: 0 auto; }
  .room-card { position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; background: var(--dark); }
  .room-card:first-child { grid-column: 1 / 2; grid-row: 1 / 3; min-height: 500px; }
  .room-card:not(:first-child) { min-height: 230px; }
  @media (max-width: 768px) {
    .room-grid { grid-template-columns: 1fr; }
    .room-card:first-child { grid-column: 1; grid-row: auto; min-height: 260px; }
    .room-card:not(:first-child) { min-height: 200px; }
    .room-section { padding: 60px 20px; }
  }
  .room-card__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
  .room-card:hover .room-card__img { transform: scale(1.07); }
  .room-card__overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,.72) 0%, rgba(0,0,0,.1) 60%); }
  .room-card__text { position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; color: #fff; z-index: 2; }
  .room-card__label { font-size: .68rem; letter-spacing: 3px; text-transform: uppercase; color: var(--gold-lt); display: block; margin-bottom: .3rem; }
  .room-card__title { font-size: 1.3rem; font-weight: 600; margin-bottom: .5rem; }
  .room-card__link {
    font-size: .78rem; color: var(--gold-lt); text-decoration: none; letter-spacing: 1px;
    text-transform: uppercase; font-weight: 600; opacity: 0; transform: translateY(6px);
    transition: all .3s; display: inline-block;
  }
  .room-card:hover .room-card__link { opacity: 1; transform: translateY(0); }

  /* ── SOCIAL PROOF ── */
  .social-proof { background: var(--white); padding: 80px 40px; }
  .stats-strip {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 1px; background: #e8e8e8; border-radius: 12px; overflow: hidden;
    max-width: 1000px; margin: 0 auto 5rem;
  }
  @media (max-width: 640px) { .stats-strip { grid-template-columns: repeat(2, 1fr); } .social-proof { padding: 60px 20px; } }
  .stat-cell { background: var(--white); padding: 2rem 1.5rem; text-align: center; }
  .stat-cell__icon { font-size: 1.4rem; color: var(--gold); margin-bottom: .5rem; display: block; }
  .stat-cell__num { font-size: 2.4rem; font-weight: 700; color: var(--dark); line-height: 1; display: block; }
  .stat-cell__lbl { font-size: .8rem; color: var(--gray); text-transform: uppercase; letter-spacing: 1.5px; margin-top: .3rem; display: block; }
  .reviews-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto; }
  .review-card { background: var(--light); border-radius: 12px; padding: 1.8rem; border-left: 3px solid var(--gold); position: relative; }
  .review-card__stars { color: var(--gold); font-size: .9rem; margin-bottom: .8rem; }
  .review-card__text { font-size: .95rem; color: var(--gray); line-height: 1.7; margin-bottom: 1.2rem; font-style: italic; }
  .review-card__author { display: flex; align-items: center; gap: .8rem; }
  .review-card__avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold); }
  .review-card__name { font-size: .9rem; font-weight: 600; color: var(--dark); display: block; }
  .review-card__loc { font-size: .78rem; color: var(--gray); }
  .review-quote { position: absolute; top: 1rem; right: 1.2rem; font-size: 3.5rem; color: var(--gold); opacity: .15; line-height: 1; font-family: Georgia, serif; }

  /* ── PROMO ── */
  .promo-banner { background: var(--dark); padding: 70px 40px; position: relative; overflow: hidden; }
  .promo-banner::before { content: ''; position: absolute; top: -60px; right: -60px; width: 400px; height: 400px; border-radius: 50%; background: var(--gold); opacity: .06; }
  .promo-banner::after  { content: ''; position: absolute; bottom: -80px; left: -40px;  width: 300px; height: 300px; border-radius: 50%; background: var(--gold); opacity: .04; }
  .promo-banner__inner { max-width: 1100px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 3rem; flex-wrap: wrap; position: relative; z-index: 2; }
  .promo-banner__left { flex: 1; min-width: 260px; }
  .promo-badge { display: inline-block; background: var(--gold); color: var(--dark); font-size: .68rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; padding: .35rem 1rem; border-radius: 2px; margin-bottom: 1rem; }
  .promo-banner__title { font-size: clamp(1.6rem, 3.5vw, 2.6rem); font-weight: 700; color: #fff; line-height: 1.2; margin-bottom: .8rem; }
  .promo-banner__title span { color: var(--gold); }
  .promo-banner__sub { color: rgba(255,255,255,.65); font-size: .95rem; line-height: 1.6; margin-bottom: 1.8rem; }
  .countdown { display: flex; gap: 12px; flex-wrap: wrap; }
  .cd-block { background: rgba(255,255,255,.08); border: 1px solid rgba(212,175,55,.3); border-radius: 8px; width: 72px; padding: .8rem .5rem; text-align: center; }
  .cd-num { font-size: 1.8rem; font-weight: 700; color: var(--gold); line-height: 1; display: block; font-variant-numeric: tabular-nums; }
  .cd-lbl { font-size: .62rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; margin-top: .3rem; display: block; }
  .promo-banner__right { display: flex; flex-direction: column; gap: .8rem; align-items: flex-start; }
  .promo-discount { font-size: 5rem; font-weight: 800; color: var(--gold); line-height: 1; display: block; }
  .promo-on { font-size: .82rem; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: 2px; }
  @media (max-width: 640px) { .promo-banner { padding: 50px 20px; } .promo-banner__inner { flex-direction: column; } .promo-discount { font-size: 3.5rem; } }

  /* ── BLOG ── */
  .blog-section { background: var(--light); padding: 80px 40px; }
  .blog-section__head { display: flex; align-items: flex-end; justify-content: space-between; max-width: 1100px; margin: 0 auto 3rem; flex-wrap: wrap; gap: 1rem; }
  .blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 28px; max-width: 1100px; margin: 0 auto; }
  .blog-card { background: var(--white); border-radius: 12px; overflow: hidden; cursor: pointer; transition: transform .3s, box-shadow .3s; }
  .blog-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.1); }
  .blog-card__img-wrap { height: 200px; overflow: hidden; position: relative; }
  .blog-card__img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
  .blog-card:hover .blog-card__img { transform: scale(1.05); }
  .blog-card__cat { position: absolute; bottom: 12px; left: 12px; background: var(--gold); color: var(--dark); font-size: .65rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; padding: .3rem .8rem; border-radius: 3px; }
  .blog-card__body { padding: 1.4rem 1.5rem 1.8rem; }
  .blog-card__meta { font-size: .75rem; color: var(--gold); margin-bottom: .6rem; display: flex; gap: .8rem; }
  .blog-card__title { font-size: 1.05rem; font-weight: 600; color: var(--dark); margin-bottom: .7rem; line-height: 1.4; }
  .blog-card__excerpt { font-size: .87rem; color: var(--gray); line-height: 1.6; margin-bottom: 1.2rem; }
  .blog-card__read { font-size: .75rem; color: var(--gold-dk); font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; text-decoration: none; border-bottom: 1px solid var(--gold-dk); padding-bottom: 2px; }
  @media (max-width: 640px) { .blog-section { padding: 60px 20px; } .blog-section__head { flex-direction: column; align-items: flex-start; } }

  /* ── FOOTER PREMIUM ── */
  .footer-premium { background: #111; color: #fff; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; }
  .footer-premium__top { border-bottom: 1px solid rgba(255,255,255,.08); padding: 60px 40px 50px; max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: 1.6fr repeat(3, 1fr); gap: 3rem; }
  @media (max-width: 900px) { .footer-premium__top { grid-template-columns: 1fr 1fr; padding: 40px 20px; } }
  @media (max-width: 560px) { .footer-premium__top { grid-template-columns: 1fr; gap: 2rem; } }
  .footer-brand__logo { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
  .footer-brand__ico { width: 44px; height: 44px; border-radius: 50%; background: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; color: var(--dark); flex-shrink: 0; }
  .footer-brand__name strong { display: block; color: #fff; font-size: 1rem; font-weight: 600; }
  .footer-brand__name span  { font-size: .78rem; color: var(--gold); }
  .footer-brand__desc { font-size: .87rem; color: rgba(255,255,255,.55); line-height: 1.7; margin-bottom: 1.4rem; }
  .footer-social { display: flex; gap: .6rem; }
  .footer-social a { width: 36px; height: 36px; border-radius: 6px; border: 1px solid rgba(255,255,255,.15); color: rgba(255,255,255,.65); display: flex; align-items: center; justify-content: center; font-size: .9rem; text-decoration: none; transition: all .25s; }
  .footer-social a:hover { border-color: var(--gold); color: var(--gold); background: rgba(212,175,55,.08); }
  .footer-col h4 { font-size: .8rem; letter-spacing: 2px; text-transform: uppercase; color: var(--gold); font-weight: 600; margin-bottom: 1.2rem; }
  .footer-col ul { list-style: none; padding: 0; }
  .footer-col ul li { margin-bottom: .55rem; }
  .footer-col ul li a { font-size: .875rem; color: rgba(255,255,255,.6); text-decoration: none; transition: color .2s; }
  .footer-col ul li a:hover { color: var(--gold); }
  .footer-contact-item { display: flex; align-items: flex-start; gap: .7rem; margin-bottom: .8rem; font-size: .87rem; color: rgba(255,255,255,.6); }
  .footer-contact-item i { color: var(--gold); margin-top: 2px; font-size: .85rem; width: 14px; flex-shrink: 0; }
  .footer-premium__middle { border-bottom: 1px solid rgba(255,255,255,.06); padding: 28px 40px; max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; }
  @media (max-width: 640px) { .footer-premium__middle { padding: 24px 20px; flex-direction: column; align-items: flex-start; } }
  .footer-seals { display: flex; gap: .6rem; flex-wrap: wrap; }
  .footer-seal { display: flex; align-items: center; gap: .4rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 6px; padding: .35rem .8rem; font-size: .73rem; color: rgba(255,255,255,.55); }
  .footer-seal i { color: var(--gold); font-size: .75rem; }
  .footer-payments { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
  .footer-payments > span { font-size: .72rem; color: rgba(255,255,255,.4); letter-spacing: 1px; text-transform: uppercase; }
  .pay-badge { background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.12); border-radius: 5px; padding: .3rem .65rem; font-size: .72rem; color: rgba(255,255,255,.65); font-weight: 600; }
  .footer-premium__bottom { padding: 22px 40px; max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .8rem; }
  @media (max-width: 640px) { .footer-premium__bottom { padding: 18px 20px; flex-direction: column; align-items: flex-start; } }
  .footer-copyright { font-size: .8rem; color: rgba(255,255,255,.3); }
  .footer-legal { display: flex; gap: 1.5rem; flex-wrap: wrap; }
  .footer-legal a { font-size: .78rem; color: rgba(255,255,255,.3); text-decoration: none; transition: color .2s; }
  .footer-legal a:hover { color: var(--gold); }

  @media (max-width: 640px) {
    .hero-refined__content { padding: 60px 20px; }
    .hero-refined__stats { gap: 1.5rem; }
  }
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
      <img class="room-card__img" src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80" alt="Sala de Estar" loading="lazy">
      <div class="room-card__overlay"></div>
      <div class="room-card__text">
        <span class="room-card__label">Ambiente</span>
        <h3 class="room-card__title">Sala de Estar</h3>
        <a href="sofa.php" class="room-card__link">Ver Sofás & Mesas →</a>
      </div>
    </div>
    <div class="room-card" data-aos="fade-left" data-aos-delay="100" id="guarda-roupa">
      <img class="room-card__img" src="https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&q=80" alt="Quarto" loading="lazy">
      <div class="room-card__overlay"></div>
      <div class="room-card__text">
        <span class="room-card__label">Ambiente</span>
        <h3 class="room-card__title">Quarto</h3>
        <a href="guarda-roupa.php" class="room-card__link">Ver Guarda-Roupas →</a>
      </div>
    </div>
    <div class="room-card" data-aos="fade-left" data-aos-delay="200" id="escrivaninha">
      <img class="room-card__img" src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=800&q=80" alt="Escritório" loading="lazy">
      <div class="room-card__overlay"></div>
      <div class="room-card__text">
        <span class="room-card__label">Ambiente</span>
        <h3 class="room-card__title">Escritório</h3>
        <a href="escrivaninha.php" class="room-card__link">Ver Escrivaninhas →</a>
      </div>
    </div>
    <div class="room-card" data-aos="fade-up" data-aos-delay="300" id="mesa-de-jantar">
      <img class="room-card__img" src="https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80" alt="Área de Jantar" loading="lazy">
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
      <p class="promo-banner__sub">Aproveite descontos exclusivos em sofás, guarda-roupas e estantes selecionados. Estoque limitado — garanta o seu agora.</p>
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
      <p class="footer-brand__desc">Transformando casas em lares especiais com móveis de qualidade, preços justos e um atendimento que você não esquece.</p>
      <div class="footer-social">
        <a href="https://www.instagram.com/realizasonhomoveis" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://wa.me/5521979771368" target="_blank" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
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
      <div class="footer-contact-item"><i class="fa-solid fa-location-dot"></i><span>Estrada do Cabuçu, 3448 - RJ</span></div>
      <div class="footer-contact-item"><i class="fa-solid fa-phone"></i><span>(21) 97977-1368</span></div>
      <div style="margin-top:1rem;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,.1);">
        <iframe src="https://www.google.com/maps?q=Estrada+do+Cabu%C3%A7u,+3448,+Rio+de+Janeiro&output=embed"
          width="100%" height="130" style="border:0;display:block;filter:grayscale(1) invert(1) brightness(.7)" allowfullscreen="" loading="lazy">
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
      if (diff <= 0) { ['cd-days','cd-hours','cd-min','cd-sec'].forEach(id => document.getElementById(id).textContent = '00'); return; }
      document.getElementById('cd-days').textContent  = pad(Math.floor(diff / 86400000));
      document.getElementById('cd-hours').textContent = pad(Math.floor((diff % 86400000) / 3600000));
      document.getElementById('cd-min').textContent   = pad(Math.floor((diff % 3600000) / 60000));
      document.getElementById('cd-sec').textContent   = pad(Math.floor((diff % 60000) / 1000));
    }
    tick(); setInterval(tick, 1000);
  })();
</script>

    <script>
        // Filtrar produtos por categoria
        document.querySelectorAll('.sessao-card').forEach(card => {
            card.addEventListener('click', function() {
                const categoria = this.dataset.categoria;
                if (categoria) {
                    loadProductsByCategory(categoria);
                    const area = document.getElementById('produtosArea');
                    if (area) area.scrollIntoView({ behavior: 'smooth' });
                }
            });
            card.style.cursor = 'pointer';
        });

        function isTrue(v) {
            return v === true || v === 't' || v === '1' || v === 1 || v === 'true';
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m];
            });
        }

        function loadProductsByCategory(categoria) {
            fetch(`produtos.php?ajax=1&categoria=${encodeURIComponent(categoria)}`)
                .then(res => res.json())
                .then(data => renderProducts(data))
                .catch(err => console.error('Erro ao carregar produtos:', err));
        }

        function renderProducts(products) {
            const container = document.getElementById('produtosArea');
            if (!container) return;
            
            if (!products || products.length === 0) {
                container.innerHTML = `<div class="sem-produtos"><h3>🔍 Nenhum produto encontrado</h3><p>Tente ajustar os filtros de busca</p></div>`;
                return;
            }

            let html = '';
            products.forEach(prod => {
                // Usar primeira_imagem que já vem do servidor
                const imgSrc = prod.primeira_imagem || (prod.imagens_array && prod.imagens_array[0]) || prod.imagem_principal || '';
                const promocao = isTrue(prod.em_promocao);
                const preco = promocao ? prod.preco_promocional : prod.preco;
                const precoFormat = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(preco);
                const precoOriginal = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(prod.preco);

                html += `
                    <div class="produto-card" onclick="window.location.href='produto-detalhes.php?id=${prod.id}'">
                        <div class="product-badge">${promocao ? 'Oferta' : escapeHtml(prod.categoria)}</div>
                        <div class="produto-imagem">
                            ${imgSrc ? `<img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(prod.nome)}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2214%22 fill=%22%23ccc%22%3ESem imagem%3C/text%3E%3C/svg%3E'">` : '<div style="color:#ccc;">Sem imagem</div>'}
                        </div>
                        <div class="produto-conteudo">
                            <span class="product-category">${escapeHtml(prod.marca)}</span>
                            <h3 class="produto-titulo">${escapeHtml(prod.nome)}</h3>
                            <p class="produto-descricao">${escapeHtml(String(prod.descricao || '').substring(0, 100))}...</p>
                            <div class="produto-preco-container">
                                ${promocao ? `<span class="preco-atual">${precoFormat}</span><span class="preco-original-riscado">${precoOriginal}</span>` : `<span class="preco-atual">${precoFormat}</span>`}
                            </div>
                            <button class="btn-comprar" onclick="event.stopPropagation(); window.location.href='produto-detalhes.php?id=${prod.id}'">VER DETALHES</button>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartCount = document.getElementById('cartCount');

        function updateCartCount() {
            cartCount.textContent = cart.reduce((total, item) => total + item.qty, 0);
        }

        updateCartCount();
    </script>
</body>
</html>