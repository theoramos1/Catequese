
function telefone_valido(num, locale)
{
    var digits = num.replace(/\D/g, "");
    console.log("Validando telefone:", digits);
    if(locale === "PT")
    {
        return /^(\+?\d{1,})?\d{9}$/.test(digits);
    }
    else if(locale === "BR")
    {
        var landline = /^\d{10}$/;              // e.g. 65993334444 => (65) 9933-4444
        var mobile = /^\d{11}$/;                // e.g. 65998003774 => (65) 99800-3774
        return (landline.test(digits) || (mobile.test(digits) && /^\d{2}9/.test(digits)));
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
    var digits = codigo.replace(/\D/g, "");
    console.log("Validando CEP:", digits);
    var pattern = "";
    if(locale==="PT")
        pattern = /^[0-9]{4}[0-9]{3}$/;
    else if(locale==="BR")
        pattern = /^[0-9]{8}$/;

    return (pattern.test(digits));
}


function data_valida(data)
{
    var pattern = /^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/;
    return (pattern.test(data));
}
