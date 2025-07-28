function telefone_valido(num, locale)
{
    var digits = (num || '').replace(/\D/g, '');
    var pattern;

    if(locale === "PT")
    {
        pattern = /^(\+\d{1,}[-\s]{0,1})?\d{9}$/;
        if(typeof window !== 'undefined' && window.console)
            console.log('telefone_valido -> locale:', locale, 'digits:', digits, 'pattern:', pattern);
        return pattern.test(num);
    }
    else if(locale === "BR")
    {
        // Accept either 11 digits for mobiles or 10 digits for landlines.
        // Mobile numbers must have the third digit equal to 9, while
        // landlines cannot start with 9 after the DDD.
        var result = false;
        if(digits.length === 11 && digits.charAt(2) === '9')
        {
            result = true;
        }
        else if(digits.length === 10 && digits.charAt(2) !== '9')
        {
            result = true;
        }

        if(typeof window !== 'undefined' && window.console)
            console.log('telefone_valido -> locale:', locale, 'digits:', digits, 'result:', result);

        return result;
    }

    if(typeof window !== 'undefined' && window.console)
    {
        if(typeof pattern !== 'undefined' && pattern)
            console.log('telefone_valido -> locale:', locale, 'digits:', digits, 'pattern:', pattern);
        else
            console.log('telefone_valido -> locale:', locale, 'digits:', digits);
    }
    return false;
}

function email_valido(email)
{
    var pattern = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return (pattern.test(email));
}

function codigo_postal_valido(codigo, locale)
{
    var digits = (codigo || '').replace(/\D/g, '');
    var pattern = "";
    if(locale==="PT")
        pattern = /^[0-9]{4}\-[0-9]{3}\s\S+/;
    else if(locale==="BR")
        // Brazilian zip code without locality
        pattern = /^[0-9]{5}\-[0-9]{3}$/;

    if(typeof window !== 'undefined' && window.console)
        console.log('codigo_postal_valido -> locale:', locale, 'digits:', digits, 'pattern:', pattern);

    return (pattern.test(codigo));
}

function data_valida(data)
{
    var pattern = /^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/;
    return (pattern.test(data));
}
