
function telefone_valido(num, locale)
{
    if(locale === "PT")
    {
        return /^(\+\d{1,}[-\s]{0,1})?\d{9}$/.test(num);
    }
    else if(locale === "BR")
    {
        const mobile = /^\(\d{2}\) 9\d{4}-\d{4}$/;
        const landline = /^\(\d{2}\) \d{4}-\d{4}$/;
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
        // Brazilian zip code without locality
        pattern = /^[0-9]{5}\-[0-9]{3}$/;

    return (pattern.test(codigo));
}


function data_valida(data)
{
    var pattern = /^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/;
    return (pattern.test(data));
}
