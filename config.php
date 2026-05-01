<?php
/**
 * CONFIGURAÇÃO DO BANCO DE DADOS - SUPABASE
 * Realiza Móveis
 * 
 * Este arquivo gerencia a conexão com o banco de dados no Supabase
 * e fornece funções utilitárias para trabalhar com imagens
 */

// ============================================
// CREDENCIAIS DO SUPABASE
// ============================================
$host     = getenv('DB_HOST'); // Substitua pelo seu host
$dbname   = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASS'); 
$port     = getenv('DB_PORT');                      // Porta padrão PostgreSQL

// ============================================
// CONEXÃO PDO COM SSL
// ============================================
// Supabase REQUER SSL (sslmode=require)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false
    ]);
    
    // Define charset para UTF-8
    $pdo->exec("SET NAMES 'utf8'");
    
} catch(PDOException $e) {
    error_log("Erro de conexão DB: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

// ============================================
// FUNÇÕES UTILITÁRIAS PARA IMAGENS
// ============================================

/**
 * Decodifica e retorna um array de URLs de imagens
 * 
 * @param string|null $imagem_principal - URL da imagem principal
 * @param string|null $imagem_secundarias - JSON com imagens secundárias
 * @param string|null $imagens - Coluna antiga com imagens (JSON)
 * @return array Array de URLs de imagens
 */
function obter_imagens($imagem_principal = null, $imagem_secundarias = null, $imagens = null) {
    $resultado = [];
    
    // 1. Adiciona imagem principal se existir
    if (!empty($imagem_principal)) {
        $resultado[] = $imagem_principal;
    }
    
    // 2. Decodifica imagem_secundarias (campo novo)
    if (!empty($imagem_secundarias)) {
        $secundarias = json_decode($imagem_secundarias, true);
        if (is_array($secundarias)) {
            $resultado = array_merge($resultado, array_filter($secundarias));
        }
    }
    
    // 3. Decodifica imagens (campo antigo para compatibilidade)
    if (!empty($imagens)) {
        $antigas = json_decode($imagens, true);
        if (is_array($antigas)) {
            $resultado = array_merge($resultado, array_filter($antigas));
        }
    }
    
    // Remove duplicatas e reindexiza
    return array_values(array_unique(array_filter($resultado)));
}

/**
 * Retorna a primeira imagem disponível (para thumbnails)
 * 
 * @param string|null $imagem_principal
 * @param string|null $imagem_secundarias
 * @param string|null $imagens
 * @return string|null Primeira URL de imagem ou null
 */
function obter_primeira_imagem($imagem_principal = null, $imagem_secundarias = null, $imagens = null) {
    $todas = obter_imagens($imagem_principal, $imagem_secundarias, $imagens);
    return !empty($todas) ? $todas[0] : null;
}

/**
 * Formata preço para exibição
 * 
 * @param float $valor
 * @return string Preço formatado como R$ X.XXX,XX
 */
function formatar_preco($valor) {
    return number_format($valor, 2, ',', '.');
}

/**
 * Formata produto com imagens decodificadas
 * 
 * @param array $produto Array com dados do produto
 * @return array Produto com imagens decodificadas
 */
function formatar_produto($produto) {
    if (is_array($produto)) {
        $produto['imagens_array'] = obter_imagens(
            $produto['imagem_principal'] ?? null,
            $produto['imagem_secundarias'] ?? null,
            $produto['imagens'] ?? null
        );
        $produto['primeira_imagem'] = obter_primeira_imagem(
            $produto['imagem_principal'] ?? null,
            $produto['imagem_secundarias'] ?? null,
            $produto['imagens'] ?? null
        );
    }
    return $produto;
}

/**
 * Formata um array de produtos
 * 
 * @param array $produtos
 * @return array Array de produtos formatados
 */
function formatar_produtos($produtos) {
    return array_map('formatar_produto', $produtos);
}

?>