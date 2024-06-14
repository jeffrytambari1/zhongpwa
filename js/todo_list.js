

// const itemsArray = localStorage.getItem('items') ? JSON.parse(localStorage.getItem('items')) : [];
// const itemsArray = localStorage.getItem('todo_items') ? JSON.parse(localStorage.getItem('todo_items')) : [];
var itemsArray; // = localStorage.getItem('todo_items') ? JSON.parse(localStorage.getItem('todo_items')) : [];

function fetchData() {
  itemsArray = localStorage.getItem('todo_items') ? JSON.parse(localStorage.getItem('todo_items')) : [];
}

// document.querySelector("#enter").addEventListener("click", () => {
document.querySelector("#todo_enter").addEventListener("click", () => {
  // const item = document.querySelector("#item");
  const item = document.querySelector("#todo_item");
  createItem(item);
})

// document.querySelector("#item").addEventListener("keypress", (e) => {
document.querySelector("#todo_item").addEventListener("keypress", (e) => {
  if(e.key === "Enter"){
    // const item = document.querySelector("#item");
    const item = document.querySelector("#todo_item");
    createItem(item);
  }
})

function displayDate(){
  let date = new Date();
  date = date.toString().split(" ");
  date = date[1] + " " + date[2] + " " + date[3] ;
  // document.querySelector("#date").innerHTML = date ;
  document.querySelector("#todo_date").innerHTML = date; 
}

function displayItems(){
  fetchData();
  let items = "";

  // let ul = document.querySelector(".to-do-list");
  let ol = document.querySelector("#todo_list");
  // console.log ( 1111, ol );

  for(let i = 0; i < itemsArray.length; i++){
    // items += `<div class="item">
    //             <div class="input-controller">
    //               <textarea disabled>${itemsArray[i]}</textarea>
    //               <div class="edit-controller">
    //                 <i class="fa-solid fa-check deleteBtn"></i>
    //                 <i class="fa-solid fa-pen-to-square editBtn"></i>
    //               </div>
    //             </div>
    //             <div class="update-controller">
    //               <button class="saveBtn">Save</button>
    //               <button class="cancelBtn">Cancel</button>
    //             </div>
    //           </div>`;
    // items += `<li class="item">
    //             <div class="input-controller">
    //               <textarea disabled>${itemsArray[i]}</textarea>
    //               <div class="edit-controller">
    //                 <i class="fa-solid fa-check deleteBtn"></i>
    //                 <i class="fa-solid fa-pen-to-square editBtn"></i>
    //               </div>
    //             </div>
    //             <div class="update-controller">
    //               <button class="saveBtn">Save</button>
    //               <button class="cancelBtn">Cancel</button>
    //             </div>
    //           </li>`;
    items += `<li class="item">
                <div class="input-controller">
                  <input type="text" value="${itemsArray[i]}" readonly/>
                  <div class="edit-controller">
                    <i class="icon1 icon_delete deleteBtn"></i>
                    <i class="icon1 icon_edit editBtn"></i>
                  </div>
                </div>
                <div class="update-controller">
                  <button class="saveBtn" type="button">Save</button>
                  <button class="cancelBtn" type="button">Cancel</button>
                </div>
              </li>`;
              // <input class="hidden_text" type="hidden" value="${itemsArray[i]}" />
              // console.log ( "itemArray " + i + " = |" + itemsArray[i] + "| " ); 
              // alert ( "itemArray " + i + " = |" + itemsArray[i] + "| " ); 

    // let li = document.createElement("li");
    // li.textContent = 1111;
    // ol.appendChild(li);
  }
  // document.querySelector(".to-do-list").innerHTML = items;
  ol.innerHTML = items;
  activateDeleteListeners();
  activateEditListeners();
  activateSaveListeners();
  activateCancelListeners();

  set_saved_urls_list_item_click();
}

function activateDeleteListeners(){
  let deleteBtn = document.querySelectorAll(".deleteBtn");
  deleteBtn.forEach((dB, i) => {
    dB.addEventListener("click", () => { deleteItem(i) })
  });
}

function activateEditListeners(){
  const editBtn = document.querySelectorAll(".editBtn");
  const updateController = document.querySelectorAll(".update-controller");
  // const inputs = document.querySelectorAll(".input-controller textarea");
  const inputs = document.querySelectorAll(".input-controller input[type=text]");
  editBtn.forEach((eB, i) => {
    eB.addEventListener("click", () => { 
      updateController[i].style.display = "block";
      // inputs[i].disabled = false;
      // inputs[i].readonly = false;
      // inputs[i].setAttribute('readonly', false); 
      inputs[i].removeAttribute('readonly'); 
    });
  })
}

function activateSaveListeners(){
  const saveBtn = document.querySelectorAll(".saveBtn");
  // const inputs = document.querySelectorAll(".input-controller textarea");
  const inputs = document.querySelectorAll(".input-controller input");
  saveBtn.forEach((sB, i) => {
    sB.addEventListener("click", () => {
      updateItem(inputs[i].value, i);
    })
  })
}

function activateCancelListeners(){
  const cancelBtn = document.querySelectorAll(".cancelBtn");
  const updateController = document.querySelectorAll(".update-controller");
  // const inputs = document.querySelectorAll(".input-controller textarea");
  const inputs = document.querySelectorAll(".input-controller input");
  cancelBtn.forEach((cB, i) => {
    cB.addEventListener("click", () => {
      updateController[i].style.display = "none";
      // inputs[i].disabled = true;
      // inputs[i].readonly = true;
      inputs[i].setAttribute('readonly', true); 
      inputs[i].style.border = "none";
    })
  })
}

function createItem(item){
  if(item.value.trim().length === 0) { return false; }
  itemsArray.push(item.value);
  // localStorage.setItem('items', JSON.stringify(itemsArray));
  localStorage.setItem('todo_items', JSON.stringify(itemsArray));
  // location.reload();
  displayItems();
}

function deleteItem(i){
  itemsArray.splice(i,1);
  // localStorage.setItem('items', JSON.stringify(itemsArray));
  localStorage.setItem('todo_items', JSON.stringify(itemsArray));
  // location.reload();
  displayItems();
}

function updateItem(text, i){
  itemsArray[i] = text;
  // localStorage.setItem('items', JSON.stringify(itemsArray));
  localStorage.setItem('todo_items', JSON.stringify(itemsArray));
  // location.reload();
  displayItems();
}


window.onload = function() {
  displayDate();
  displayItems();
};


