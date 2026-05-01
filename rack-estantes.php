<?php
$host     = 'aws-1-sa-east-1.pooler.supabase.com'; // Substitua pelo seu host
$dbname   = 'postgres';
$user     = 'postgres.gmjdsvjedcvdvwfganxi';
$password = '5vt7UGaOMQqsvPri'; 
$port     = '6543';                      // Porta padrão PostgreSQL

// ============================================
// CONEXÃO PDO COM SSL
// ============================================
// Supabase REQUER SSL (sslmode=require)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT * 
        FROM produtos 
        WHERE categoria = :categoria OR categoria = 'rack'
        AND status = 'ativo'
        ORDER BY id DESC
    ");
    $stmt->bindValue(':categoria', 'estante');
    $stmt->execute();

    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rack e Estantes - Realiza Móveis</title>

    <link rel="icon" type="image/svg+xml" href="assets/imgs/logoModificada.svg">

    <link rel="stylesheet" href="assets/css/cardsPromo.css">
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="assets/js/cliqueCards.js" defer></script>
    <script src="assets/js/mostrarCards.js" defer></script>
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="top-bar-item">
                <img src="assets/imgs/locBranco.svg" alt="Localização">
                <a href="#">Estrada do Cabuçu 3448, São Paulo</a>
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
        <a href="index.php" class="nav-link">
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

    <?php if (empty($produtos)): ?>
        <div class="sem-produtos">
            <h3>🔍 Nenhum produto encontrado</h3>
            <p>Tente ajustar os filtros de busca</p>
        </div>
    <?php else: ?>
      <div id="produtosArea" class="produtos-grid">
            <?php foreach ($produtos as $produto): ?>
              <?php if (isset($produto['status']) && $produto['status'] !== 'ativo') continue; ?>
                <div class="produto-card" onclick="window.location.href='produto-detalhes.php?id=<?php echo $produto['id']; ?>'">

                    <div class="product-badge">
                        <?php echo $produto['em_promocao'] ? 'Oferta' : htmlspecialchars($produto['categoria']); ?>
                    </div>

                    <div class="produto-imagem">
                      <?php
                      // Sempre usar a imagem principal definida no admin quando disponível
                      if (!empty($produto['imagem_principal'])) {
                          $imgSrc = $produto['imagem_principal'];
                      } elseif (isset($produto['imagens']) && !empty($produto['imagens'])) {
                          $decoded = json_decode($produto['imagens'], true) ?: [];
                          $imgSrc = count($decoded) ? $decoded[0] : '';
                      } elseif (isset($produto['imagem_secundarias']) && !empty($produto['imagem_secundarias'])) {
                          $decoded = json_decode($produto['imagem_secundarias'], true) ?: [];
                          $imgSrc = count($decoded) ? $decoded[0] : '';
                      } else {
                          $imgSrc = '';
                      }
                      if ($imgSrc):
                      ?>
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                      <?php else: ?>
                        <div style="color: #ccc;">Sem imagem</div>
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
                                <span class="preco-atual">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></span>
                                <span class="preco-original-riscado">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <?php else: ?>
                                <span class="preco-atual">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
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
    <footer>
      <div class="footer-container">
        <div class="footer-section">
          <div class="footer-logo">
            <div class="footer-logo-icon">R</div>
            <div class="footer-logo-text">
              <strong>Realiza</strong>
              <span>Móveis</span>
            </div>
          </div>
          <p>Móveis de qualidade para transformar sua casa num lar especial há
            mais de 10 anos.</p>
        </div>

        <div class="footer-section">
          <h3>Links Rápidos</h3>
          <ul>
            <li><a href="#">Produtos</a></li>
            <li><a href="#">Sala de Estar</a></li>
            <li><a href="#">Sobre Nós</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Contato</h3>
          <div class="footer-contact">
            <span>📍</span>
            <div>
              <div>Estrada do Cabuçu 3448</div>
            </div>
          </div>
          <div class="footer-contact">
            <span>📞</span>
            <div>(21) 97977-1368</div>
          </div>
          <div class="footer-contact">
            <span>✉️</span>
            <div>contato@realizamoveis.com.br</div>
          </div>
        </div>

        <div class="footer-section">
          <h3>Redes Sociais</h3>
          <div class="social-links">
            <a
              href="https://www.instagram.com/realizasonhomoveis?igsh=YmF1NXFiaTNjeWM4&utm_source=qr"
              target="_blank" title="Instagram">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                <path
                  d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <circle cx="17.5" cy="6.5" r="1.5"></circle>
              </svg>
            </a>
            <a href="https://wa.me/message/DGFVY3FNTHA5B1" target="_blank"
              title="WhatsApp">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path
                  d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <div class="footer-copyright">
        © 2026 Realiza Móveis. Todos os direitos reservados.
      </div>
    </footer>
</body>
</html>
