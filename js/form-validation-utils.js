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
        const mobile = /^\d{2}9\d{8}$/;
        const landline = /^\d{2}\d{8}$/;

        pattern = mobile;
        var result = mobile.test(digits);
        if(!result)
        {
            pattern = landline;
            result = landline.test(digits);
        }

        if(typeof window !== 'undefined' && window.console)
            console.log('telefone_valido -> locale:', locale, 'digits:', digits, 'pattern:', pattern);

        return result;
    }

    if(typeof window !== 'undefined' && window.console)
        console.log('telefone_valido -> locale:', locale, 'digits:', digits, 'pattern:', pattern);
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
