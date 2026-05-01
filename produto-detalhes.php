<?php
/**
 * PÁGINA DE DETALHES DO PRODUTO - REALIZA MÓVEIS
 * Exibe informações completas de um produto com galeria de imagens
 */

require_once 'config.php';

// ============================================
// BUSCAR PRODUTO
// ============================================
$produto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id AND status = 'ativo'");
    $stmt->execute([':id' => $produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        header('Location: produtos.php');
        exit;
    }
    
    // Formatar o produto com imagens decodificadas
    $produto = formatar_produto($produto);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar produto: " . $e->getMessage());
    header('Location: produtos.php');
    exit;
}

// ============================================
// BUSCAR PRODUTOS RELACIONADOS
// ============================================
try {
    $stmt = $pdo->prepare("SELECT * FROM produtos 
                          WHERE categoria = :categoria 
                          AND id != :id 
                          AND status = 'ativo' 
                          ORDER BY data_cadastro DESC 
                          LIMIT 4");
    $stmt->execute([
        ':categoria' => $produto['categoria'],
        ':id' => $produto_id
    ]);
    $relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $relacionados = formatar_produtos($relacionados);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar produtos relacionados: " . $e->getMessage());
    $relacionados = [];
}

// ============================================
// BUSCAR VARIANTES (MESMO MODELO, CORES DIFERENTES)
// ============================================
try {
    $stmt = $pdo->prepare("SELECT id, nome, preco, preco_promocional, em_promocao, 
                                  marca, modelo, descricao, sku, cor, cor_hex, 
                                  imagem_principal, imagem_secundarias
                          FROM produtos 
                          WHERE modelo = :modelo 
                          AND status = 'ativo' 
                          AND id != :id
                          ORDER BY cor ASC");
    $stmt->execute([
        ':modelo' => $produto['modelo'],
        ':id' => $produto_id
    ]);
    $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar variantes com imagens
    foreach ($variantes as &$v) {
        $v['imagens_array'] = obter_imagens(
            $v['imagem_principal'] ?? null,
            $v['imagem_secundarias'] ?? null
        );
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar variantes: " . $e->getMessage());
    $variantes = [];
}

// ============================================
// PREPARAR MENSAGEM WHATSAPP
// ============================================
$whatsapp_numero = '5521979771368';
$preco_display = $produto['em_promocao'] ? $produto['preco_promocional'] : $produto['preco'];
$mensagem_whatsapp = urlencode(
    "Olá! Gostaria de encomendar o seguinte produto:\n\n" .
    "📦 *Produto:* " . $produto['nome'] . "\n" .
    "🏷️ *Marca:* " . $produto['marca'] . "\n" .
    "🔢 *Modelo:* " . $produto['modelo'] . "\n" .
    "📋 *Código (SKU):* " . $produto['sku'] . "\n" .
    "💰 *Preço:* R$ " . formatar_preco($preco_display) . "\n\n" .
    "Aguardo retorno!"
);
$link_whatsapp = "https://wa.me/{$whatsapp_numero}?text={$mensagem_whatsapp}";

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome']); ?> - Realiza Móveis</title>
    <link rel="icon" type="image/svg+xml" href="assets/imgs/logoModificada.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/css/cardsPromo.css">
    <style>
        .detalhes-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .breadcrumb {
            margin-bottom: 30px;
            font-size: 0.9em;
            color: #666;
        }

        .breadcrumb a {
            color: var(--gold);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .produto-detalhes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }

        /* GALERIA DE IMAGENS */
        .galeria-imagens {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .imagem-principal {
            width: 100%;
            height: 500px;
            background: #f5f5f5;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .imagem-principal img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .miniaturas {
            display: grid;
            grid-template-columns: repeat(4, 140px);
            gap: 10px;
            justify-content: start;
            padding-bottom: 4px;
        }

        .miniatura {
            width: 140px;
            height: 90px;
            background: #f5f5f5;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }

        .miniatura:hover,
        .miniatura.ativa {
            border-color: var(--gold);
        }

        .miniatura img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* OPÇÕES DE CORES */
        .opcoes-cores {
            margin-top: 18px;
        }

        .opcoes-cores h4 {
            margin: 0 0 8px 0;
            font-size: 0.95em;
            color: var(--dark);
        }

        .swatches {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .swatch {
            display: inline-flex;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid transparent;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            cursor: pointer;
            text-decoration: none;
        }

        .swatch img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .swatch-circle {
            width: 100%;
            height: 100%;
            display: block;
        }

        .swatch.selected {
            border-color: var(--gold);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.18);
        }

        /* INFORMAÇÕES DO PRODUTO */
        .info-produto {
            display: flex;
            flex-direction: column;
        }

        .produto-categoria {
            color: var(--gold);
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .produto-nome {
            font-size: 2.5em;
            color: var(--dark);
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .produto-marca {
            font-size: 1.1em;
            color: #666;
            margin-bottom: 20px;
        }

        .produto-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge.promocao {
            background: #E74C3C;
            color: white;
        }

        .badge.destaque {
            background: #FF9800;
            color: white;
        }

        .badge.categoria {
            background: var(--gold);
            color: white;
        }

        .preco-box {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .preco-original {
            text-decoration: line-through;
            color: #999;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .preco-atual {
            font-size: 3em;
            color: var(--gold);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .preco-promocional {
            font-size: 3em;
            color: #E74C3C;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .economia {
            color: #4CAF50;
            font-size: 1em;
            font-weight: 600;
        }

        .descricao-produto {
            margin-bottom: 30px;
            line-height: 1.8;
            color: #555;
        }

        .descricao-produto h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        .especificacoes {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .especificacoes h3 {
            color: var(--dark);
            margin-bottom: 20px;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .spec-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .spec-label {
            font-weight: 600;
            color: var(--gold);
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .spec-value {
            color: var(--dark);
            font-size: 1em;
        }

        .btn-encomendar {
            width: 100%;
            padding: 20px;
            background: #25D366;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.3em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-encomendar:hover {
            background: #1fb855;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        }

        .btn-carrinho {
            width: 100%;
            padding: 20px;
            background: var(--gold);
            color: var(--dark);
            border: none;
            border-radius: 12px;
            font-size: 1.3em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .btn-carrinho:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-carrinho.adicionado {
            background: #4CAF50;
            color: white;
        }

        .info-adicional {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            border-radius: 8px;
            font-size: 0.9em;
            color: #2e7d32;
        }

        .relacionados {
            margin-top: 60px;
        }

        .relacionados h2 {
            font-size: 2em;
            color: var(--dark);
            margin-bottom: 30px;
            text-align: center;
        }

        .relacionados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        @media (max-width: 968px) {
            .produto-detalhes {
                grid-template-columns: 1fr;
            }

            .spec-grid {
                grid-template-columns: 1fr;
            }

            .produto-nome {
                font-size: 2em;
            }
        }

        @media (max-width: 640px) {
            .miniaturas {
                grid-template-columns: repeat(2, 120px);
                gap: 8px;
            }
            .miniatura {
                width: 120px;
                height: 80px;
            }
            .imagem-principal {
                height: 360px;
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


        <!-- DETALHES DO PRODUTO -->
        <div class="produto-detalhes">
            <!-- GALERIA DE IMAGENS -->
            <div class="galeria-imagens">
                <div class="imagem-principal" id="imagemPrincipal">
                    <?php if (!empty($produto['imagens_array']) && count($produto['imagens_array']) > 0): ?>
                        <img src="<?php echo htmlspecialchars($produto['imagens_array'][0]); ?>" 
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                             id="imgPrincipal"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2220%22 fill=%22%23ccc%22%3ESem imagem%3C/text%3E%3C/svg%3E'">
                    <?php else: ?>
                        <div style="color: #ccc;">Sem imagem</div>
                    <?php endif; ?>
                </div>

                <!-- MINIATURAS -->
                <div class="miniaturas">
                    <?php if (!empty($produto['imagens_array']) && count($produto['imagens_array']) > 0): ?>
                        <?php foreach ($produto['imagens_array'] as $index => $imagem): ?>
                            <div class="miniatura <?php echo $index === 0 ? 'ativa' : ''; ?>" 
                                 onclick="trocarImagem('<?php echo htmlspecialchars($imagem); ?>', this)">
                                <img src="<?php echo htmlspecialchars($imagem); ?>" 
                                     alt="Imagem <?php echo $index + 1; ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22140%22 height=%2290%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22140%22 height=%2290%22/%3E%3C/svg%3E'">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- OPÇÕES DE CORES -->
                <?php if (!empty($variantes)): ?>
                <div class="opcoes-cores">
                    <h4>Outras cores disponíveis</h4>
                    <div class="swatches">
                        <?php foreach ($variantes as $v): 
                            $thumb = !empty($v['imagens_array']) ? $v['imagens_array'][0] : '';
                            $color_label = htmlspecialchars($v['cor'] ?? '');
                            $color_hex = htmlspecialchars($v['cor_hex'] ?? '');
                            $data_images = htmlspecialchars(json_encode($v['imagens_array'] ?? []), ENT_QUOTES, 'UTF-8');
                            $data_descr = htmlspecialchars($v['descricao'] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                        <a class="swatch" href="#" 
                           data-variant-id="<?php echo $v['id']; ?>" 
                           data-nome="<?php echo htmlspecialchars($v['nome'], ENT_QUOTES); ?>" 
                           data-preco="<?php echo $v['preco']; ?>" 
                           data-preco-promocional="<?php echo $v['preco_promocional']; ?>" 
                           data-em-promocao="<?php echo $v['em_promocao'] ? '1' : '0'; ?>" 
                           data-marca="<?php echo htmlspecialchars($v['marca'], ENT_QUOTES); ?>" 
                           data-modelo="<?php echo htmlspecialchars($v['modelo'], ENT_QUOTES); ?>" 
                           data-sku="<?php echo htmlspecialchars($v['sku'], ENT_QUOTES); ?>" 
                           data-images='<?php echo $data_images; ?>' 
                           data-descricao='<?php echo $data_descr; ?>' 
                           data-cor="<?php echo $color_label; ?>" 
                           data-cor-hex="<?php echo $color_hex; ?>" 
                           title="<?php echo $color_label; ?>">
                            <?php if (!empty($color_hex)): ?>
                                <span class="swatch-circle" style="background: <?php echo htmlspecialchars($color_hex); ?>;"></span>
                            <?php elseif (!empty($thumb)): ?>
                                <img src="<?php echo htmlspecialchars($thumb); ?>" 
                                     alt="<?php echo $color_label; ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2242%22 height=%2242%22%3E%3Crect fill=%22%23ccc%22 width=%2242%22 height=%2242%22/%3E%3C/svg%3E'">
                            <?php else: ?>
                                <span class="swatch-circle" style="background:#ccc;"></span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- DESCRIÇÃO -->
                <div class="descricao-produto">
                    <h3>Descrição</h3>
                    <p><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>
                </div>
            </div>

            <!-- INFORMAÇÕES DO PRODUTO -->
            <div class="info-produto">
                <div class="produto-categoria"><?php echo htmlspecialchars($produto['categoria']); ?></div>
                
                <h1 class="produto-nome"><?php echo htmlspecialchars($produto['nome']); ?></h1>
                
                <div class="produto-marca">
                    <strong>Marca:</strong> <?php echo htmlspecialchars($produto['marca']); ?> | 
                    <strong>Modelo:</strong> <?php echo htmlspecialchars($produto['modelo']); ?>
                </div>

                <div class="produto-badges">
                    <?php if ($produto['em_promocao']): ?>
                        <span class="badge promocao">🔥 Em Promoção</span>
                    <?php endif; ?>
                    <?php if ($produto['destaque']): ?>
                        <span class="badge destaque">⭐ Destaque</span>
                    <?php endif; ?>
                    <span class="badge categoria"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                </div>

                <!-- PREÇO -->
                <div class="preco-box">
                    <?php if ($produto['em_promocao']): ?>
                        <div class="preco-original">R$ <?php echo formatar_preco($produto['preco']); ?></div>
                        <div class="preco-promocional">R$ <?php echo formatar_preco($produto['preco_promocional']); ?></div>
                        <div class="economia">
                            💰 Economize R$ <?php echo formatar_preco($produto['preco'] - $produto['preco_promocional']); ?> 
                            (<?php echo htmlspecialchars($produto['desconto_percentual']); ?>% OFF)
                        </div>
                    <?php else: ?>
                        <div class="preco-atual">R$ <?php echo formatar_preco($produto['preco']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- BOTÃO ADICIONAR AO CARRINHO -->
                <button class="btn-carrinho" id="btnCarrinho" onclick="adicionarAoCarrinho()">
                    🛒 Adicionar ao Carrinho
                </button>

                <!-- BOTÃO ENCOMENDAR VIA WHATSAPP -->
                <a href="<?php echo htmlspecialchars($link_whatsapp); ?>" target="_blank" class="btn-encomendar">
                    <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Encomendar via WhatsApp
                </a>

                <div class="info-adicional">
                    <strong>✅ Ao clicar, você será redirecionado para o WhatsApp</strong> com uma mensagem pré-preenchida contendo:
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Nome do produto</li>
                        <li>Modelo e código (SKU)</li>
                        <li>Preço atual</li>
                    </ul>
                </div>

                <!-- ESPECIFICAÇÕES TÉCNICAS -->
                <div class="especificacoes">
                    <h3>Especificações Técnicas</h3>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <span class="spec-label">📦 Código (SKU)</span>
                            <span class="spec-value"><?php echo htmlspecialchars($produto['sku']); ?></span>
                        </div>
                        
                        <?php if ($produto['cor']): ?>
                        <div class="spec-item">
                            <span class="spec-label">🎨 Cor</span>
                            <span class="spec-value"><?php echo htmlspecialchars($produto['cor']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($produto['material']): ?>
                        <div class="spec-item">
                            <span class="spec-label">📌 Material</span>
                            <span class="spec-value"><?php echo htmlspecialchars($produto['material']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($produto['dimensoes']): ?>
                        <div class="spec-item">
                            <span class="spec-label">📐 Dimensões</span>
                            <span class="spec-value"><?php echo htmlspecialchars($produto['dimensoes']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($produto['peso']): ?>
                        <div class="spec-item">
                            <span class="spec-label">⚖️ Peso</span>
                            <span class="spec-value"><?php echo htmlspecialchars($produto['peso']); ?> kg</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="spec-item">
                            <span class="spec-label">📊 Estoque</span>
                            <span class="spec-value">
                                <?php 
                                if ($produto['estoque'] > 10) {
                                    echo "✅ Disponível";
                                } elseif ($produto['estoque'] > 0) {
                                    echo "⚠️ Últimas unidades ({$produto['estoque']} disponíveis)";
                                } else {
                                    echo "❌ Indisponível";
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUTOS RELACIONADOS -->
        <?php if (!empty($relacionados)): ?>
        <div class="relacionados">
            <h2>🛋️ Produtos Relacionados</h2>
            <div class="relacionados-grid">
                <?php foreach ($relacionados as $rel): ?>
                    <div class="produto-card" onclick="window.location.href='produto-detalhes.php?id=<?php echo $rel['id']; ?>'">
                        <div class="product-badge">
                            <?php echo $rel['em_promocao'] ? 'Oferta' : htmlspecialchars($rel['categoria']); ?>
                        </div>

                        <div class="produto-imagem">
                            <?php if (!empty($rel['primeira_imagem'])): ?>
                                <img src="<?php echo htmlspecialchars($rel['primeira_imagem']); ?>" 
                                     alt="<?php echo htmlspecialchars($rel['nome']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2214%22 fill=%22%23ccc%22%3ESem imagem%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                                <div style="color: #ccc;">Sem imagem</div>
                            <?php endif; ?>
                        </div>

                        <div class="produto-conteudo">
                            <span class="product-category"><?php echo htmlspecialchars($rel['marca']); ?></span>
                            <h3 class="produto-titulo"><?php echo htmlspecialchars($rel['nome']); ?></h3>

                            <div class="produto-preco-container">
                                <?php if ($rel['em_promocao']): ?>
                                    <span class="preco-atual">R$ <?php echo formatar_preco($rel['preco_promocional']); ?></span>
                                    <span class="preco-original-riscado">R$ <?php echo formatar_preco($rel['preco']); ?></span>
                                <?php else: ?>
                                    <span class="preco-atual">R$ <?php echo formatar_preco($rel['preco']); ?></span>
                                <?php endif; ?>
                            </div>

                            <button class="btn-comprar" onclick="event.stopPropagation(); window.location.href='produto-detalhes.php?id=<?php echo $rel['id']; ?>'">
                                VER DETALHES
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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

        let currentVariant = {
            id: <?php echo $produto['id']; ?>,
            nome: '<?php echo addslashes($produto['nome']); ?>',
            preco: <?php echo $produto['em_promocao'] ? $produto['preco_promocional'] : $produto['preco']; ?>,
            preco_original: <?php echo $produto['preco']; ?>,
            imagem: '<?php echo !empty($produto['imagens_array']) ? addslashes($produto['imagens_array'][0]) : ''; ?>',
            marca: '<?php echo addslashes($produto['marca']); ?>',
            modelo: '<?php echo addslashes($produto['modelo']); ?>',
            sku: '<?php echo $produto['sku']; ?>'
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.swatch').forEach(s => {
                s.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.swatch').forEach(sw => sw.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    const images = JSON.parse(this.getAttribute('data-images') || '[]');
                    const nome = this.getAttribute('data-nome') || '';
                    const preco = parseFloat(this.getAttribute('data-preco')) || 0;
                    const precoProm = parseFloat(this.getAttribute('data-preco-promocional')) || 0;
                    const emProm = this.getAttribute('data-em-promocao') === '1';
                    const marca = this.getAttribute('data-marca') || '';
                    const modelo = this.getAttribute('data-modelo') || '';
                    const sku = this.getAttribute('data-sku') || '';
                    const vid = this.getAttribute('data-variant-id');

                    const imgPrincipalEl = document.getElementById('imgPrincipal');
                    if (images.length > 0) {
                        imgPrincipalEl.src = images[0];
                    }

                    const miniaturasContainer = document.querySelector('.miniaturas');
                    if (miniaturasContainer) {
                        miniaturasContainer.innerHTML = '';
                        images.forEach((im, idx) => {
                            const div = document.createElement('div');
                            div.className = 'miniatura' + (idx === 0 ? ' ativa' : '');
                            div.style.cursor = 'pointer';
                            div.addEventListener('click', () => trocarImagem(im, div));
                            const img = document.createElement('img');
                            img.src = im;
                            img.alt = 'Imagem ' + (idx+1);
                            div.appendChild(img);
                            miniaturasContainer.appendChild(div);
                        });
                    }

                    const nomeEl = document.querySelector('.produto-nome');
                    if (nomeEl) nomeEl.textContent = nome;
                    const marcaEl = document.querySelector('.produto-marca');
                    if (marcaEl) marcaEl.innerHTML = `<strong>Marca:</strong> ${marca} | <strong>Modelo:</strong> ${modelo}`;
                    
                    const precoBox = document.querySelector('.preco-box');
                    if (precoBox) {
                        const fmt = v => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(v));
                        if (emProm) {
                            precoBox.innerHTML = `<div class="preco-original">${fmt(preco)}</div><div class="preco-promocional">${fmt(precoProm)}</div>`;
                        } else {
                            precoBox.innerHTML = `<div class="preco-atual">${fmt(preco)}</div>`;
                        }
                    }

                    const descP = document.querySelector('.descricao-produto p');
                    if (descP) {
                        const descr = this.getAttribute('data-descricao') || '';
                        descP.innerHTML = descr.replace(/\n/g, '<br>');
                    }

                    currentVariant = { id: parseInt(vid) || currentVariant.id, nome, preco: emProm ? precoProm : preco, preco_original: preco, imagem: images[0] || currentVariant.imagem, marca, modelo, sku };
                });
            });
        });

        function updateCartCount() {
            cartCount.textContent = cart.reduce((total, item) => total + item.qty, 0);
        }

        function trocarImagem(src, elemento) {
            document.getElementById('imgPrincipal').src = src;
            document.querySelectorAll('.miniatura').forEach(mini => {
                mini.classList.remove('ativa');
            });
            elemento.classList.add('ativa');
        }

        function adicionarAoCarrinho() {
            const produto = {
                id: currentVariant.id,
                nome: currentVariant.nome,
                preco: currentVariant.preco,
                preco_original: currentVariant.preco_original,
                imagem: currentVariant.imagem,
                marca: currentVariant.marca,
                modelo: currentVariant.modelo,
                sku: currentVariant.sku,
                qty: 1
            };

            const existingProduct = cart.find(item => item.id === produto.id);
            
            if (existingProduct) {
                existingProduct.qty += 1;
            } else {
                cart.push(produto);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();

            const btn = document.getElementById('btnCarrinho');
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Adicionado ao Carrinho!';
            btn.classList.add('adicionado');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('adicionado');
            }, 2000);

            alert('Produto adicionado ao carrinho com sucesso!');
        }

        updateCartCount();
    </script>
</body>
</html>