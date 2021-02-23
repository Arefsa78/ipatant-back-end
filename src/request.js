function sendAjaxRequest(method,url,dataToSend){ /// for requests with authorization!
    let xhttp=new XMLHttpRequest();
    let returned=null;
    xhttp.onreadystatechange=function () {
        if(this.readyState==4){
            if(this.status==200 || this.status==201){
                returned=JSON.parse(this.responseText);
            }
            else{
                let response=JSON.parse(this.responseText);
                returned=response;
                if(response=="invalid token!") {
                    returned="login";
                }
                else{
                    if(response=="expired token!") {
                        let data1 = refreshRequest();
                        if (data1 === false) {
                            returned= "login";
                        }
                        localStorage.removeItem("accessToken");
                        localStorage.setItem("accessToken",data1)
                        data1=repeatRequest(method,url,dataToSend);
                        returned=data1;
                    }
                }
            }
        }
    };
    xhttp.open(method,url,false)
    head="Bearer "+window.localStorage.getItem('accessToken');
    xhttp.setRequestHeader("Authorization",head);
    xhttp.setRequestHeader("Content-Type","application/json; charset=UTF-8");
    if(dataToSend==null) xhttp.send()
    else xhttp.send(dataToSend)
    return returned;
}


function repeatRequest(method,url,data){
    let xmlHttpRequest=new XMLHttpRequest();
    let response=null;
    xmlHttpRequest.onreadystatechange=function () {
        if(this.readyState==4){
            response=JSON.parse(this.responseText);
        }
    }
    xmlHttpRequest.open(method,url,false)
    head="Bearer "+window.localStorage.getItem('accessToken');
    xmlHttpRequest.setRequestHeader("Authorization",head);
    xmlHttpRequest.setRequestHeader("Content-Type","application/json; charset=UTF-8");
    if(data==null) xmlHttpRequest.send()
    else xmlHttpRequest.send(data)
    return response;
}

function refreshRequest(){
    let xmlHttpRequest=new XMLHttpRequest();
    let returned=null;
    xmlHttpRequest.onreadystatechange=function(){
        if(this.readyState==4){
            if(this.status==200) returned= JSON.parse(this.responseText);
            else {
                returned= false;
                window.localStorage.removeItem("accessToken")
            }
        }
    }
    xmlHttpRequest.open("GET","http://localhost/telephone_project/Controller/mainController.php/refresh",false);
    head="Bearer "+window.localStorage.getItem('accessToken');
    xmlHttpRequest.setRequestHeader("Authorization",head);
    xmlHttpRequest.setRequestHeader("Content-Type","application/json; charset=UTF-8");
    xmlHttpRequest.send();
    return returned;
}


function logoutRequest(){
        data=sendAjaxRequest("DELETE","http://localhost/telephone_project/Controller/mainController.php/logout",null);
        if(data=="ok") window.localStorage.removeItem("accessToken");
}




