async function fetchJSON(url) {
  const response = await fetch(url);
  const data = await response.json();
  return JSON.stringify(data.channel.item)
}
function create(a,b,c){
const e=document.getElementById("table");
//e.innerHTML=a+b+c;
//document.body.append(e);
let bbb='<div id="win" style="height:fit-content;overflow:hidden;position:relative;"> News From The New York Times <div id="win" style="overflow:hidden;max-width:fit-content;"> <div id="bar"> <h1>'+a+'</h1> </div> <a href='+b+'>'+c+'</a> <hr> </div></div> ';
  let joinb=bbb; 
  const fragmentb = document.createRange().createContextualFragment(joinb); 

e.appendChild(fragmentb);
  
  document.body.appendChild(e);

}
let data = fetchJSON('https://thenewmanagementinc.com/tor/serve/news.php').then(res =>{
console.log("k",JSON.parse(res));
let x= JSON.parse(res);
const items = x; // get the array of items

for (let i = 0; i < items.length; i++) {
  const title = items[i].title; // get the title of the current item
    const link = items[i].link; // get the title of the current item
    const description = items[i].description; // get the title of the current item
create(title,link,description);
  console.log('s',link); // do whatever you want with the title
}
});