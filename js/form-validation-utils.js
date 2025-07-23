
function telefone_valido(num, locale)
{
    if(locale === "PT")
    {
        return /^(\+\d{1,}[-\s]{0,1})?\d{9}$/.test(num);
    }
    else if(locale === "BR")
    {
        const mobile = /^(\+\d{1,}[-\s]{0,1})?\s*\(?(\d{2}|\d{0})\)?[-. ]?9\d{4}[-. ]?\d{4}\s*$/;
        const landline = /^(\+\d{1,}[-\s]{0,1})?\s*\(?(\d{2}|\d{0})\)?[-. ]?\d{4}[-. ]?\d{4}\s*$/;
        return mobile.test(num) || landline.test(num);
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
    var pattern = "";
    if(locale==="PT")
        pattern = /^[0-9]{4}\-[0-9]{3}\s\S+/;
    else if(locale==="BR")
        // Brazilian zip code with optional locality
        pattern = /^[0-9]{5}\-[0-9]{3}(\s\S+)?/;

    return (pattern.test(codigo));
}


function data_valida(data)
{
    var pattern = /^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/;
    return (pattern.test(data));
}
