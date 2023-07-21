// document.addEventListener("DOMContentLoaded", function (event) {

// });
require([
    'jquery',
    'jquery/ui',
    "Magento_Ui/js/modal/alert",
    'Magento_Customer/js/customer-data'
], function (
    $,
    ui,
    alert,
    customerData
) {
    const titleOption = "Pay Partially";
    // const _isLoggedIn = () => {
    //     var customerInfo = customerData.get('customer')();					   
    //     return customerInfo.firstname && customerInfo.fullname; 	
    // }
    const elements = document.querySelectorAll("#product-options-wrapper > div > div");
    elements.forEach((element) => {
        const innerText = element.innerText;
        if (innerText.toLowerCase().includes(titleOption.toLowerCase())) {
            element.classList.add("partial-payment-checkbox");

            // element.innerHTML += `<div class="help">
            //     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 6a3.939 3.939 0 0 0-3.934 3.934h2C10.066 8.867 10.934 8 12 8s1.934.867 1.934 1.934c0 .598-.481 1.032-1.216 1.626a9.208 9.208 0 0 0-.691.599c-.998.997-1.027 2.056-1.027 2.174V15h2l-.001-.633c.001-.016.033-.386.441-.793.15-.15.339-.3.535-.458.779-.631 1.958-1.584 1.958-3.182A3.937 3.937 0 0 0 12 6zm-1 10h2v2h-2z"></path><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path></svg>
            // </div>`;

            const helpButton = document.createElement('div');
            helpButton.classList.add("help")
            /*helpButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 6a3.939 3.939 0 0 0-3.934 3.934h2C10.066 8.867 10.934 8 12 8s1.934.867 1.934 1.934c0 .598-.481 1.032-1.216 1.626a9.208 9.208 0 0 0-.691.599c-.998.997-1.027 2.056-1.027 2.174V15h2l-.001-.633c.001-.016.033-.386.441-.793.15-.15.339-.3.535-.458.779-.631 1.958-1.584 1.958-3.182A3.937 3.937 0 0 0 12 6zm-1 10h2v2h-2z"></path><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path></svg>`;
            helpButton.onclick = () => {
                        alert({
                            title: $.mage.__("Partial payment"),
                            content: $.mage.__("Guarantee your reservation with a partial payment"),
                            actions: { always: function () { } },
                        });
            }*/
            helpButton.innerHTML = '<span class="tooltip-box tooltip-multiplier show">\
            <span class="label-tooltip info-tooltip"></span>\
                <span class="tooltip info-tooltip-content">'
                + $.mage.__("Guarantee your reservation with a partial payment") + ' 👍' +
                '</span>\
            </span>';
            //element.appendChild(helpButton);

            element.querySelector(".title-option").style.display = "none";
            element.querySelector(".label").innerHTML += "<span>"+$.mage.__("Pay Partially")+"</span>";
	    element.querySelector(".label").appendChild(helpButton);
            // let inputCheckbox = element.querySelector("input");
            // inputCheckbox.disabled = true;

            // customerData.get('customer').subscribe((value)=>{
            //     if (_isLoggedIn()) {
            //         inputCheckbox.disabled = false;
            //     }
            // })
            // element.onclick = () => {
            //     if (!_isLoggedIn()) {
            //         alert({
            //             title: $.mage.__("Login Required"),
            //             content: "You must be logged to enable partial payment function",
            //             actions: { always: function () { } },
            //         });
            //     }
            // }

        }
    })
});
