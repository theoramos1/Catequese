function applyBrazilianMasks() {
    if (typeof $ === 'undefined' || typeof $.fn.mask !== 'function') {
        return;
    }
    $('#telefone').mask('(00) 0000-0000');
    $('#codigo_postal').mask('99999-999');
    $('#telemovel').mask('(00) Z 0000-0000', {
        translation: {
            'Z': { pattern: /9/ },
            '0': { pattern: /[0-9]/ }
        }
    });
    $('[name^="autorizacao_telefone"], .autorizacao-telefone').mask('(00) Z 0000-0000', {
        translation: {
            'Z': { pattern: /9/ },
            '0': { pattern: /[0-9]/ }
        }
    });
}

