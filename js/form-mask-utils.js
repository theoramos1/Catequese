function applyBrazilianMasks() {
    if (typeof $ === 'undefined' || typeof $.fn.mask !== 'function') {
        return;
    }
    $('#telefone').mask('(00) 0000-0000');
    $('#codigo_postal').mask('99999-999');
    $('#telemovel').mask('(00) 90000-0000');
    $('[name^="autorizacao_telefone"], .autorizacao-telefone').mask('(00) 90000-0000');
}

