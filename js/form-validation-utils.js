
function telefone_valido(num, locale)
{
    locale = (locale || '').trim().toUpperCase();
    console.log('telefone_valido locale:', locale);

    if(locale === "PT")
    {
        const pattern = /^(\+\d{1,}[-\s]{0,1})?\d{9}$/;
        console.log('telefone_valido regex:', pattern);
        return pattern.test(num);
    }
    else if(locale === "BR")
    {
        num = num.replace(/\D/g, '');
        const mobile = /^\d{2}9\d{8}$/;
        const landline = /^\d{2}\d{8}$/;
        console.log('telefone_valido regex:', mobile, 'or', landline);
        return mobile.test(num) || landline.test(num);
    }

    console.log('telefone_valido regex: none');
    return false;
}

function email_valido(email)
{
    var pattern = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return (pattern.test(email));
}

function codigo_postal_valido(codigo, locale)
{
    locale = (locale || '').trim().toUpperCase();
    console.log('codigo_postal_valido locale:', locale);
    var pattern = "";
    if(locale==="PT")
        pattern = /^[0-9]{4}\-[0-9]{3}\s\S+/;
    else if(locale==="BR")
        // Brazilian zip code without locality
        pattern = /^[0-9]{5}\-[0-9]{3}$/;

    console.log('codigo_postal_valido regex:', pattern);
    return (pattern.test(codigo));
}


function data_valida(data)
{
    var pattern = /^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/;
    return (pattern.test(data));
}
