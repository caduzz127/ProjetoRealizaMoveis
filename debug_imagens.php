<?php
/**
 * DEBUG - Verificar Imagens no Banco
 * Acesse: http://localhost/realizaMoveis/debug_imagens.php
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug - Imagens</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .product { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product h2 { margin-top: 0; color: #007bff; }
        .field { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #007bff; }
        .field-label { font-weight: bold; color: #555; }
        .field-value { color: #333; word-break: break-all; font-family: monospace; font-size: 12px; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .warning { color: #ff9800; }
        .img-preview { margin-top: 10px; }
        .img-preview img { max-width: 200px; height: auto; border-radius: 4px; margin-right: 10px; margin-bottom: 10px; }
        .no-image { color: #999; font-style: italic; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        table th { background: #007bff; color: white; }
        .json-valid { background: #d4edda; }
        .json-invalid { background: #f8d7da; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Debug - Verificar Imagens</h1>";

try {
    // ============================================
    // 1. VERIFICAR ESTRUTURA DA TABELA
    // ============================================
    echo "<h2>1️⃣ Estrutura da Tabela</h2>";
    
    $query = "SELECT column_name, data_type FROM information_schema.columns 
              WHERE table_name = 'produtos' 
              ORDER BY ordinal_position";
    
    $result = $pdo->query($query);
    $columns = $result->fetchAll();
    
    if (empty($columns)) {
        echo "<p class='error'>❌ Tabela 'produtos' não encontrada!</p>";
    } else {
        echo "<table>";
        echo "<tr><th>Coluna</th><th>Tipo</th></tr>";
        foreach ($columns as $col) {
            $highlight = in_array($col['column_name'], ['imagem_principal', 'imagem_secundarias', 'imagens']) ? 'style="background: #fff3cd;"' : '';
            echo "<tr $highlight>";
            echo "<td><strong>" . $col['column_name'] . "</strong></td>";
            echo "<td>" . $col['data_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // ============================================
    // 2. CONTAR PRODUTOS
    // ============================================
    echo "<h2>2️⃣ Produtos no Banco</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
    $result = $stmt->fetch();
    $total = $result['total'];
    
    echo "<p>Total de produtos: <strong>$total</strong></p>";
    
    if ($total == 0) {
        echo "<p class='error'>❌ Nenhum produto no banco! Você precisa inserir dados primeiro.</p>";
    }

    // ============================================
    // 3. VERIFICAR PRIMEIROS PRODUTOS
    // ============================================
    echo "<h2>3️⃣ Primeiros 5 Produtos (Detalhes de Imagens)</h2>";
    
    $stmt = $pdo->query("SELECT id, nome, imagem_principal, imagem_secundarias 
                         FROM produtos 
                         LIMIT 5");
    $produtos = $stmt->fetchAll();
    
    if (empty($produtos)) {
        echo "<p class='error'>❌ Nenhum produto encontrado!</p>";
    } else {
        foreach ($produtos as $p) {
            echo "<div class='product'>";
            echo "<h2>ID: {$p['id']} - {$p['nome']}</h2>";
            
            // ============================================
            // CAMPO: imagem_principal
            // ============================================
            echo "<div class='field'>";
            echo "<div class='field-label'>📸 imagem_principal:</div>";
            
            if (empty($p['imagem_principal'])) {
                echo "<div class='field-value error'>❌ VAZIO</div>";
            } else {
                echo "<div class='field-value success'>✅ PREENCHIDO</div>";
                echo "<div class='field-value'>" . htmlspecialchars($p['imagem_principal']) . "</div>";
                echo "<div class='img-preview'>";
                echo "<img src='" . htmlspecialchars($p['imagem_principal']) . "' alt='Imagem principal' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22%3EErro ao carregar%3C/text%3E%3C/svg%3E'\">";
                echo "</div>";
            }
            echo "</div>";
            
            // ============================================
            // CAMPO: imagem_secundarias
            // ============================================
            echo "<div class='field'>";
            echo "<div class='field-label'>📸 imagem_secundarias (JSON):</div>";
            
            if (empty($p['imagem_secundarias'])) {
                echo "<div class='field-value error'>❌ VAZIO</div>";
            } else {
                $decoded = json_decode($p['imagem_secundarias'], true);
                
                if ($decoded === null) {
                    echo "<div class='field-value json-invalid'>⚠️ JSON INVÁLIDO</div>";
                    echo "<div class='field-value'>" . htmlspecialchars($p['imagem_secundarias']) . "</div>";
                } else {
                    echo "<div class='field-value json-valid'>✅ JSON VÁLIDO - " . count($decoded) . " imagens</div>";
                    echo "<div class='field-value'>";
                    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
                    echo "</div>";
                    
                    echo "<div class='img-preview'>";
                    foreach ($decoded as $img) {
                        if (!empty($img)) {
                            echo "<img src='" . htmlspecialchars($img) . "' alt='Secundária' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2212%22%3EErro%3C/text%3E%3C/svg%3E'\">";
                        }
                    }
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // ============================================
            // CAMPO: imagens (antigo)
            // ============================================
            echo "<div class='field'>";
            echo "<div class='field-label'>📸 imagens (campo antigo - JSON):</div>";
            
            if (empty($p['imagens'])) {
                echo "<div class='field-value error'>❌ VAZIO</div>";
            } else {
                $decoded = json_decode($p['imagens'], true);
                
                if ($decoded === null) {
                    echo "<div class='field-value json-invalid'>⚠️ JSON INVÁLIDO</div>";
                    echo "<div class='field-value'>" . htmlspecialchars($p['imagens']) . "</div>";
                } else {
                    echo "<div class='field-value json-valid'>✅ JSON VÁLIDO - " . count($decoded) . " imagens</div>";
                    echo "<div class='field-value'>";
                    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // ============================================
            // RESULTADO FINAL: formatar_produto()
            // ============================================
            echo "<div class='field' style='background: #e3f2fd; border-left-color: #2196f3;'>";
            echo "<div class='field-label'>📌 Resultado Final (formatar_produto):</div>";
            
            $p_formatado = formatar_produto($p);
            
            echo "<div style='margin-top: 10px;'>";
            echo "<strong>primeira_imagem:</strong><br>";
            if (empty($p_formatado['primeira_imagem'])) {
                echo "<span class='error'>❌ NENHUMA IMAGEM</span>";
            } else {
                echo "<span class='success'>✅ " . htmlspecialchars($p_formatado['primeira_imagem']) . "</span>";
            }
            echo "</div>";
            
            echo "<div style='margin-top: 10px;'>";
            echo "<strong>imagens_array (total: " . count($p_formatado['imagens_array']) . "):</strong><br>";
            if (empty($p_formatado['imagens_array'])) {
                echo "<span class='error'>❌ NENHUMA IMAGEM</span>";
            } else {
                echo "<span class='success'>✅ " . count($p_formatado['imagens_array']) . " imagens encontradas</span>";
                echo "<div class='field-value'>";
                echo "<pre>" . json_encode($p_formatado['imagens_array'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
                echo "</div>";
            }
            echo "</div>";
            
            echo "</div>";
            
            echo "</div>";
        }
    }

    // ============================================
    // 4. RESUMO
    // ============================================
    echo "<h2>4️⃣ Resumo & Diagnóstico</h2>";
    
    $stmt = $pdo->query("SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN imagem_principal IS NOT NULL AND imagem_principal != '' THEN 1 ELSE 0 END) as com_principal,
                            SUM(CASE WHEN imagem_secundarias IS NOT NULL AND imagem_secundarias != '' THEN 1 ELSE 0 END) as com_secundarias
                        FROM produtos");
    $stats = $stmt->fetch();
    
    echo "<table>";
    echo "<tr><th>Métrica</th><th>Valor</th></tr>";
    echo "<tr><td>Total de produtos</td><td>{$stats['total']}</td></tr>";
    echo "<tr><td>Com imagem_principal</td><td>{$stats['com_principal']} (" . ($stats['total'] > 0 ? round(($stats['com_principal'] / $stats['total']) * 100) : 0) . "%)</td></tr>";
    echo "<tr><td>Com imagem_secundarias</td><td>{$stats['com_secundarias']} (" . ($stats['total'] > 0 ? round(($stats['com_secundarias'] / $stats['total']) * 100) : 0) . "%)</td></tr>";
    echo "<tr><td>Com imagens (antigo)</td><td>{$stats['com_imagens']} (" . ($stats['total'] > 0 ? round(($stats['com_imagens'] / $stats['total']) * 100) : 0) . "%)</td></tr>";
    echo "</table>";
    
    echo "<h3>📊 Análise:</h3>";
    
    if ($stats['total'] == 0) {
        echo "<p class='error'><strong>❌ PROBLEMA:</strong> Nenhum produto no banco!</p>";
        echo "<p><strong>Solução:</strong> Você precisa inserir produtos no banco de dados com imagens.</p>";
    } else if ($stats['com_principal'] == 0 && $stats['com_secundarias'] == 0 && $stats['com_imagens'] == 0) {
        echo "<p class='error'><strong>❌ PROBLEMA:</strong> Nenhum produto tem imagens!</p>";
        echo "<p><strong>Solução:</strong> Adicione URLs de imagem nos campos 'imagem_principal' ou 'imagem_secundarias'.</p>";
    } else {
        echo "<p class='success'><strong>✅ OK:</strong> Produtos têm imagens!</p>";
        echo "<p><strong>Próxima verificação:</strong> As imagens devem aparecer no site. Se não aparecerem, a URL pode estar quebrada.</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'><strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>
</body>
</html>";
?>