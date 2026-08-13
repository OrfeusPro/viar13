document.addEventListener("DOMContentLoaded", function(event) {
    // header mobile lang
    // const lang_sel = document.querySelector('.js__lang');
    // lang_sel.addEventListener('change', (event) => {
    //   var link = event.target.value;
    //   document.location = link;
    //   console.log(event);
    // });

    // form input mask
    setTimeout(function () {
        var input = document.querySelector("#phone2");

        var count_ar = [
            "al",
            "ad",
            "at",
            "by",
            "be",
            "ba",
            "bg",
            "hr",
            "cz",
            "dk",
            "ee",
            "fo",
            "fi",
            "fr",
            "de",
            "gi",
            "gr",
            "va",
            "hu",
            "is",
            "ie",
            "it",
            "lv",
            "li",
            "lt",
            "lu",
            "mk",
            "mt",
            "md",
            "mc",
            "me",
            "nl",
            "no",
            "pl",
            "pt",
            "ro",
            "ru",
            "sm",
            "rs",
            "sk",
            "si",
            "es",
            "se",
            "ch",
            "ua",
            "gb",
        ];

        if(window.intlTelInput){
            if(input){
                window.intlTelInput(input, {
                    initialCountry: "lv",
                    onlyCountries: count_ar,
                    utilsScript: "/js/utils.js?1613236686837",
                });
            }

        }
    }, 4000);


});
