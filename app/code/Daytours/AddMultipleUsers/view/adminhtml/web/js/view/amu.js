define([
    'uiComponent',
    'ko'
], function (uiComponent, ko) {
    const usuariosProcesados = {
        procesados: 0,
        totales: 0
    }
    return uiComponent.extend({
        initialize: function () {
            this._super();
        },
        procesarXml: function (e) {
            try {
                document.querySelector("#containerUsuariosProcesados").innerHTML = "";
                this.mostrarError(false);
                usuariosProcesados.procesados = 0;
                usuariosProcesados.totales = 0;
                let usuariosToSave = [];
                const file = e[0].files[0];
                if(!file){
                    throw {message:"Please, select a file to continue."};
                }
                const reader = new FileReader();
                reader.addEventListener('load', async (event) => {
                    try {
                        const xmlParser = new DOMParser();
                        const xmlDoc = xmlParser.parseFromString(event.target.result, "text/xml");
                        const users = xmlDoc.getElementsByTagName("user");
                        if(!users.length){
                            throw {message:"There are not users in this file. Please check the format."};
                        }
                        for (let i = 0; i < users.length; i++) {
                            let user = {
                                email: users[i].childNodes[1].textContent,
                                password: users[i].childNodes[3].textContent,
                            };
                            await usuariosToSave.push(user);
                        }
                        let results = await this.enviarUsuarios(usuariosToSave);
                        this.mostrarUsuariosProcesados(results);
                    } catch (error) { 
                        console.error(error)
                        this.mostrarError(true,error?.message);
                    }
                })
                reader.readAsText(file);
            } catch (error) {
                this.mostrarError(true,error?.message);
            }

        },
        enviarUsuarios: async function(arrayUsuarios){
            try {
                let response = await fetch(
                    "https://" + location.hostname + "/rest/V1/addmultipleusers/",
                    {
                        body: JSON.stringify({
                            users: arrayUsuarios
                        }),
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                let jsonData = await response.json();
                console.log(jsonData);
                return jsonData;
            } catch (error) {
                this.mostrarError(true,error?.message);
                console.error(error);
                Promise.reject(jsonData);
            }
        },
        mostrarUsuariosProcesados : function(usuarios = []){
            let container = document.querySelector("#containerUsuariosProcesados");
            usuarios.forEach((current)=>{
                const fila = document.createElement("div");
                fila.className = current.ok ? "usuario-exito" : "usuario-error";
                fila.innerHTML =  "<b>User: </b>"+current.user+" | <b>Message: </b>"+current.message;
                container.appendChild(fila);
            })
        },
        mostrarError(mostrar, mensaje = "") {
            let errorElement = document.querySelector("#avisoError");
            if (mostrar) {
                errorElement.removeAttribute("hidden");
                errorElement.innerText = mensaje;
            }
            else {
                errorElement.setAttribute("hidden", true);
            }
        }
    })
})