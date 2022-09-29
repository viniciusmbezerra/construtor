class Cookie {
    static addCookie(nome, valor){ document.cookie = nome + '=' + valor + '; path=/'; }
    static getIsCookie(nome){ return (document.cookie.split('; ').find(row => row.startsWith(nome + '='))?.split('=')[1].length > 0) ? true : false; }
    static getCookie(nome){ return document.cookie.split('; ').find(row => row.startsWith(nome + '='))?.replace(nome + '=', ''); }
    static setCookie(nome, valor){ document.cookie = nome + '=' + valor + '; path=/'; return true; }
    static clearCookie(nome){ document.cookie = nome + '=;path=/;'; }
    static deleteCookie(nome){
        var data = new Date();
        // Converte a data para GMT
        // Apaga o cookie
        document.cookie = nome + '=; expires=Mon, 18 Dec 1995 17:28:35 GMT; path=/';
    }
    static countRow(nome){ if(this.getIsCookie(nome)){ var texto =  this.getCookie(nome); var remover = texto.split('</br>'); return (remover.length + 1); } else { return 0; }}
}