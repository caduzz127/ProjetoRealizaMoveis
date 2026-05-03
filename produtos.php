<?php
/**
 * PÁGINA DE PRODUTOS - REALIZA MÓVEIS
 * Lista todos os produtos com filtros e busca
 */

require_once 'config.php';

// ============================================
// FILTROS
// ============================================
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$marca = isset($_GET['marca']) ? trim($_GET['marca']) : '';
$preco_min = isset($_GET['preco_min']) ? floatval($_GET['preco_min']) : '';
$preco_max = isset($_GET['preco_max']) ? floatval($_GET['preco_max']) : '';
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$material = isset($_GET['material']) ? trim($_GET['material']) : '';
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'destaque';
$apenas_promocao = isset($_GET['apenas_promocao']) ? true : false;
$apenas_destaque = isset($_GET['apenas_destaque']) ? true : false;

// ============================================
// CONSTRUIR QUERY COM FILTROS
// ============================================
$query = "SELECT * FROM produtos WHERE status = 'ativo'";
$params = [];

if ($categoria) {
    $query .= " AND categoria ILIKE :categoria";
    $params[':categoria'] = "%$categoria%";
}

if ($marca) {
    $query .= " AND marca ILIKE :marca";
    $params[':marca'] = "%$marca%";
}

if ($preco_min) {
    $query .= " AND (CASE WHEN em_promocao THEN preco_promocional ELSE preco END) >= :preco_min";
    $params[':preco_min'] = $preco_min;
}

if ($preco_max) {
    $query .= " AND (CASE WHEN em_promocao THEN preco_promocional ELSE preco END) <= :preco_max";
    $params[':preco_max'] = $preco_max;
}

if ($cor) {
    $query .= " AND cor ILIKE :cor";
    $params[':cor'] = "%$cor%";
}

if ($material) {
    $query .= " AND material ILIKE :material";
    $params[':material'] = "%$material%";
}

if ($busca) {
    $query .= " AND (nome ILIKE :busca OR descricao ILIKE :busca OR modelo ILIKE :busca)";
    $params[':busca'] = "%$busca%";
}

if ($apenas_promocao) {
    $query .= " AND em_promocao = true";
}

if ($apenas_destaque) {
    $query .= " AND destaque = true";
}

// ============================================
// ORDENAÇÃO
// ============================================
switch ($ordenar) {
    case 'menor_preco':
        $query .= " ORDER BY CASE WHEN em_promocao THEN preco_promocional ELSE preco END ASC";
        break;
    case 'maior_preco':
        $query .= " ORDER BY CASE WHEN em_promocao THEN preco_promocional ELSE preco END DESC";
        break;
    case 'promocao':
        $query .= " ORDER BY em_promocao DESC, desconto_percentual DESC";
        break;
    case 'nome':
        $query .= " ORDER BY nome ASC";
        break;
    default: // destaque
        $query .= " ORDER BY destaque DESC, em_promocao DESC, data_cadastro DESC";
}

// ============================================
// EXECUTAR QUERY
// ============================================
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar todos os produtos com imagens decodificadas
    $produtos = formatar_produtos($produtos);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar produtos: " . $e->getMessage());
    $produtos = [];
}

// ============================================
// BUSCAR FILTROS DISPONÍVEIS
// ============================================
try {
    $categorias = $pdo->query("SELECT DISTINCT categoria FROM produtos WHERE status = 'ativo' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
    $marcas = $pdo->query("SELECT DISTINCT marca FROM produtos WHERE status = 'ativo' ORDER BY marca")->fetchAll(PDO::FETCH_COLUMN);
    $cores = $pdo->query("SELECT DISTINCT cor FROM produtos WHERE status = 'ativo' AND cor IS NOT NULL AND cor != '' ORDER BY cor")->fetchAll(PDO::FETCH_COLUMN);
    $materiais = $pdo->query("SELECT DISTINCT material FROM produtos WHERE status = 'ativo' AND material IS NOT NULL AND material != '' ORDER BY material")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Erro ao buscar filtros: " . $e->getMessage());
    $categorias = $marcas = $cores = $materiais = [];
}

// ============================================
// RETORNAR JSON PARA AJAX
// ============================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($produtos);
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Realiza Móveis</title>
    <link rel="icon" type="image/svg+xml" href="assets/imgs/logoModificada.svg">
    <link rel="stylesheet" href="assets/css/cardsPromo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content {
            display: flex;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .sidebar-filtros {
            width: 320px;
            flex-shrink: 0;
            position: sticky;
            top: 20px;
            align-self: start;
        }

        .sidebar-filtros-inner {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 20px 45px rgba(0,0,0,0.1);
            border: 1px solid rgba(201,163,78,0.15);
        }

        .sidebar-panel-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 18px;
            padding-bottom: 10px;
        }

        .sidebar-panel-top h3 {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--dark);
        }

        .sidebar-open-btn,
        .sidebar-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            background: rgba(201,163,78,0.12);
            color: var(--gold);
            padding: 12px 16px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .sidebar-close-btn {
            width: 42px;
            height: 42px;
            padding: 0;
            border-radius: 14px;
        }

        .sidebar-open-btn {
            display: none;
            width: 100%;
            justify-content: center;
            margin-bottom: 20px;
        }

        .sidebar-open-btn i {
            font-size: 1rem;
        }

        .filter-block {
            margin-bottom: 16px;
            border-radius: 18px;
            overflow: hidden;
            background: #faf9f5;
            border: 1px solid rgba(229, 218, 171, 0.5);
        }

        .accordion {
            width: 100%;
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 18px;
            font-size: 0.98rem;
            color: var(--dark);
            font-weight: 700;
            cursor: pointer;
            position: relative;
            transition: background 0.25s ease;
        }

        .accordion:hover {
            background: rgba(201,163,78,0.08);
        }

        .accordion span {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .accordion i {
            transition: transform 0.3s ease;
        }

        .accordion.active i {
            transform: rotate(180deg);
        }

        .panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, padding 0.25s ease;
            padding: 0 18px;
        }

        .panel.show {
            max-height: 500px;
            padding-top: 14px;
            padding-bottom: 18px;
        }

        .panel .filtro-busca,
        .panel .filtro-group,
        .panel .sidebar-menu {
            display: grid;
            gap: 14px;
        }

        .filtro-group select,
        .filtro-group input,
        .filtro-busca input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #E0E0E0;
            border-radius: 12px;
            font-size: 0.96em;
            transition: all 0.3s;
            background: #fff;
        }

        .filtro-busca input:focus,
        .filtro-group select:focus,
        .filtro-group input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(201,163,78,0.12);
        }

        .sidebar-menu {
            display: grid;
            gap: 12px;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid rgba(229, 218, 171, 0.5);
            color: var(--dark);
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .sidebar-menu-item:hover {
            transform: translateX(2px);
            background: rgba(201,163,78,0.12);
            border-color: rgba(201,163,78,0.35);
        }

        .sidebar-menu-item i {
            color: var(--gold);
        }

        .mobile-sidebar-action {
            display: none;
            margin-bottom: 18px;
        }

        @media (max-width: 968px) {
            .main-content {
                flex-direction: column;
            }

            .sidebar-filtros {
                width: 280px;
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 95;
                padding: 18px 16px;
                background: transparent;
                transition: left 0.3s ease;
            }

            .sidebar-filtros.open {
                left: 0;
            }

            .sidebar-filtros-inner {
                height: 100%;
                overflow-y: auto;
                padding-bottom: 30px;
            }

            .content-area {
                width: 100%;
            }

            .mobile-sidebar-action {
                display: block;
            }

            .sidebar-open-btn {
                display: inline-flex;
            }

            .sidebar-close-btn {
                display: inline-flex;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.45);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
                z-index: 90;
            }

            .sidebar-overlay.active {
                opacity: 1;
                pointer-events: all;
            }
        }

        .btn-filtrar {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--gold) 0%, #B8941F 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-filtrar:hover {
            background: linear-gradient(135deg, #B8941F 0%, var(--gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 163, 78, 0.4);
        }

        .btn-limpar {
            width: 100%;
            padding: 10px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-limpar:hover {
            background: #5a6268;
        }

        .content-area {
            flex: 1;
        }

        .produtos-header {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .produtos-header h2 {
            color: var(--dark);
            font-size: 1.5em;
            margin: 0;
        }

        .resultado-count {
            color: #666;
            font-size: 0.95em;
        }

        .checkbox-filtro {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            cursor: pointer;
        }

        .checkbox-filtro input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        @media (max-width: 968px) {
            .main-content {
                flex-direction: column;
            }

            .sidebar-filtros {
                width: 100%;
                position: static;
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
        <a href="index.php" class="nav-link">
            <i class="fas fa-home"></i>
            <span>Início</span>
        </a>
        <a href="produtos.php" class="nav-link active">
            <i class="fas fa-couch"></i>
            <span>Produtos</span>
        </a>
        <a href="https://wa.me/5521979771368" class="nav-link" target="_blank">
            <i class="fas fa-envelope"></i>
            <span>Contato</span>
        </a>
    </nav>

    <div class="main-content">
        <div class="mobile-sidebar-action">
            <button id="sidebarOpenBtn" class="sidebar-open-btn">
                <i class="fas fa-bars"></i>
                Filtrar
            </button>
        </div>

        <!-- SIDEBAR DE FILTROS -->
        <aside class="sidebar-filtros" id="sidebarFiltros">
            <div class="sidebar-filtros-inner">
                <div class="sidebar-panel-top">
                    <h3>Filtrar resultados</h3>
                    <button type="button" id="sidebarCloseBtn" class="sidebar-close-btn" aria-label="Fechar menu">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="GET" id="filtrosForm">
                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-magnifying-glass"></i> Buscar</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-busca">
                                <input type="text" name="busca" placeholder="Nome, modelo ou descrição..." 
                                       value="<?php echo htmlspecialchars($busca); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-th-large"></i> Categorias</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="sidebar-menu">
                                <?php foreach ($categorias as $cat): ?>
                                    <a href="produtos.php?categoria=<?php echo urlencode($cat); ?>" class="sidebar-menu-item">
                                        <span><?php echo ucfirst(htmlspecialchars($cat)); ?></span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-tag"></i> Marca</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-group">
                                <select name="marca" onchange="this.form.submit()">
                                    <option value="">Todas as Marcas</option>
                                    <?php foreach ($marcas as $m): ?>
                                        <option value="<?php echo htmlspecialchars($m); ?>" 
                                            <?php echo $marca == $m ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-dollar-sign"></i> Faixa de Preço</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-group">
                                <label>Preço Mínimo (R$)</label>
                                <input type="number" name="preco_min" placeholder="0" step="0.01" 
                                       value="<?php echo htmlspecialchars($preco_min); ?>">
                            </div>
                            <div class="filtro-group">
                                <label>Preço Máximo (R$)</label>
                                <input type="number" name="preco_max" placeholder="10000" step="0.01" 
                                       value="<?php echo htmlspecialchars($preco_max); ?>">
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($cores)): ?>
                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-palette"></i> Cor</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-group">
                                <select name="cor" onchange="this.form.submit()">
                                    <option value="">Todas as Cores</option>
                                    <?php foreach ($cores as $c): ?>
                                        <option value="<?php echo htmlspecialchars($c); ?>" 
                                            <?php echo $cor == $c ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($materiais)): ?>
                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-leaf"></i> Material</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-group">
                                <select name="material" onchange="this.form.submit()">
                                    <option value="">Todos os Materiais</option>
                                    <?php foreach ($materiais as $mat): ?>
                                        <option value="<?php echo htmlspecialchars($mat); ?>" 
                                            <?php echo $material == $mat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-star"></i> Especiais</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="checkbox-filtro">
                                <input type="checkbox" id="apenas_promocao" name="apenas_promocao" value="1" 
                                       <?php echo $apenas_promocao ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label for="apenas_promocao">🔥 Apenas Promoções</label>
                            </div>
                            <div class="checkbox-filtro">
                                <input type="checkbox" id="apenas_destaque" name="apenas_destaque" value="1" 
                                       <?php echo $apenas_destaque ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label for="apenas_destaque">⭐ Apenas Destaques</label>
                            </div>
                        </div>
                    </div>

                    <div class="filter-block">
                        <button type="button" class="accordion active">
                            <span><i class="fas fa-arrow-up-a-z"></i> Ordenar por</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </button>
                        <div class="panel show">
                            <div class="filtro-group">
                                <select name="ordenar" onchange="this.form.submit()">
                                    <option value="destaque" <?php echo $ordenar == 'destaque' ? 'selected' : ''; ?>>⭐ Destaques</option>
                                    <option value="promocao" <?php echo $ordenar == 'promocao' ? 'selected' : ''; ?>>🔥 Promoções</option>
                                    <option value="menor_preco" <?php echo $ordenar == 'menor_preco' ? 'selected' : ''; ?>>💵 Menor Preço</option>
                                    <option value="maior_preco" <?php echo $ordenar == 'maior_preco' ? 'selected' : ''; ?>>💰 Maior Preço</option>
                                    <option value="nome" <?php echo $ordenar == 'nome' ? 'selected' : ''; ?>>🔤 Nome (A-Z)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-filtrar">Aplicar Filtros</button>
                    <a href="produtos.php" class="btn-limpar" style="text-decoration: none; text-align: center; display: block;">
                        Limpar Filtros
                    </a>
                </form>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="content-area">
            <div class="produtos-header">
                <h2>Nossos Produtos</h2>
                <span class="resultado-count">
                    <?php echo count($produtos); ?> produto(s) encontrado(s)
                </span>
            </div>

            <?php if (empty($produtos)): ?>
                <div class="sem-produtos">
                    <h3>🔍 Nenhum produto encontrado</h3>
                    <p>Tente ajustar os filtros de busca</p>
                    <a href="produtos.php" style="color: var(--gold); text-decoration: underline; margin-top: 10px; display: inline-block;">
                        Ver todos os produtos
                    </a>
                </div>
            <?php else: ?>
                <div class="produtos-grid">
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
        </div>
    </div>

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
                <p>Móveis de qualidade para transformar sua casa num lar especial há mais de 10 anos.</p>
            </div>

            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="#">Sobre Nós</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contato</h3>
                <div class="footer-contact">
                    <span>📍</span>
                    <div>Estrada do Cabuçu 3448</div>
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
                    <a href="https://www.instagram.com/realizasonhomoveis" target="_blank" title="Instagram">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <circle cx="17.5" cy="6.5" r="1.5"></circle>
                        </svg>
                    </a>
                    <a href="https://wa.me/5521979771368" target="_blank" title="WhatsApp">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            © 2026 Realiza Móveis. Todos os direitos reservados.
        </div>
    </footer>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartCount = document.getElementById('cartCount');

        function updateCartCount() {
            cartCount.textContent = cart.reduce((total, item) => total + item.qty, 0);
        }

        updateCartCount();

        const sidebar = document.getElementById('sidebarFiltros');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const accordions = document.querySelectorAll('.accordion');

        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        }

        function openSidebar() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('active');
        }

        sidebarOpenBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);

        accordions.forEach(button => {
            button.addEventListener('click', () => {
                const panel = button.nextElementSibling;
                const isOpen = button.classList.contains('active');

                button.classList.toggle('active');
                if (panel) {
                    if (isOpen) {
                        panel.classList.remove('show');
                    } else {
                        panel.classList.add('show');
                    }
                }
            });
        });
    </script>
</body>
</html>