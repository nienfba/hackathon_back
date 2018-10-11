function requeteXHR(route, dataPost, action){
    let reqXhr = new XMLHttpRequest();
    let data = dataPost;
    reqXhr.open('POST', route);
    reqXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    reqXhr.addEventListener('load', function(){action(reqXhr.responseText)});
    reqXhr.send(data);
}