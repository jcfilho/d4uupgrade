/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'uiClass'
], function (Class) {
    'use strict';

    return Class.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            return this;
        },
        getCurrentLocationLineCode(error){
            let arrData = error.stack.split(" ").pop()
            return arrData;
        },
        sendErrorLog: function(moduleName,message, location, moreDetails = ""){
            const body = {
                data:{
                    moduleName:moduleName,
                    message:message? message.substr(0,256) : "(No message error provided)",
                    location,
                    moreDetails:moreDetails ? moreDetails : "(No details provided)",
                    date:(new Date()).toString()
                }
            }
            fetch(window.location.origin+"/rest/V1/errorlogs/",{
                method:"POST",
                headers:{
                    "Content-Type":"application/json"
                },
                body:JSON.stringify(body)
            })
            // .then(async (res)=>{
            //     //console.log("RES: ",res);
            //     //const text = await res.text();
            //     //console.log("TEXT: ",text);
            // })
            .catch((err)=>{
                console.error("REQ ERROR: ",err);
            })
        }
    });
});
