<?php
/**
 * Template para páginas do Hotel Flor de Lima
 * 
 * Como usar:
 * 1. Defina as variáveis de configuração da página
 * 2. Inclua este arquivo no início da página
 * 3. Use include 'includes/header.php' e include 'includes/footer.php'
 * 
 * Exemplo:
 * 
 * <?php
 * // Configuração da página
 * $pageTitle = 'Nome da Página';
 * $pageDescription = 'Descrição da página para SEO';
 * $additionalCSS = ['assets/css/pagina.css'];
 * $additionalJS = ['assets/js/pagina.js'];
 * $bodyClass = 'classe-customizada'; // Opcional
 * 
 * // Incluir template
 * include 'includes/page-template.php';
 * 
 * // Include header
 * include 'includes/header.php';
 * ?>
 * 
 * <!-- Conteúdo da página aqui -->
 * 
 * <?php
 * // Include footer
 * include 'includes/footer.php';
 * ?>
 */

// Verificar se as variáveis necessárias foram definidas
if (!isset($pageTitle)) {
    $pageTitle = 'Hotel Flor de Lima';
}

if (!isset($pageDescription)) {
    $pageDescription = 'Uma experiência única de hospedagem e gastronomia, onde tradições eslavas e japonesas se encontram.';
}

// Inicializar arrays se não foram definidos
if (!isset($additionalCSS)) {
    $additionalCSS = [];
}

if (!isset($additionalJS)) {
    $additionalJS = [];
}

if (!isset($bodyClass)) {
    $bodyClass = '';
}

// Adicionar CSS de navegação se não estiver incluído
if (!in_array('assets/css/navigation.css', $additionalCSS)) {
    array_unshift($additionalCSS, 'assets/css/navigation.css');
}

// Adicionar JavaScript de navegação se não estiver incluído
if (!in_array('assets/js/navigation.js', $additionalJS)) {
    array_unshift($additionalJS, 'assets/js/navigation.js');
}

// Função helper para adicionar JavaScript inline
function addPageJS($js) {
    global $pageJS;
    $pageJS = $js;
}

// Função helper para adicionar CSS inline
function addPageCSS($css) {
    global $pageCSS;
    $pageCSS = $css;
}
?>
