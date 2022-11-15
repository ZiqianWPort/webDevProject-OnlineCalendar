let btn = document.getElementById("modal-btn");
let btnLog = document.getElementById("modal-btn-log");

let modal = document.getElementById("myModal");
let modalLog = document.getElementById("myModal-log");
let modalEdit = document.getElementById("myModal-edit");

$('.close').click(function() {
    modal.style.display = "none";
    modalLog.style.display = "none";
    modalEdit.style.display = "none";
})

btn.onclick = function () {
    modal.style.display = "block";
}
btnLog.onclick = function () {
    modalLog.style.display = "block";
}

window.onclick = function (event) {
    if ((event.target == modal)||(event.target == modalLog) || (event.target == modalEdit)) {
        modal.style.display = "none";
        modalLog.style.display = "none";
        modalEdit.style.display = "none";
    }
}

updateCalendar();

let date = "1900=00-00";
function addEvent(event) {
    let allTags = ["work","home","school"];
    let username = $('#hidden-username').val();
    let id = $('#hidden-id').val();
    let token = $('#hidden-token').val();
    date = $("#event_date").val();
    let title = $("#event_title").val();
    let members = $("#event_members").val();
    if (!title || title == ''){
        title = "null title";
    }
  
    let time = $("#event_time").val();
    let selectedTags = "";
    if ($('#event_tag1').is(":checked")){
        selectedTags += '@'+(allTags[0]);
    }
    if ($('#event_tag2').is(":checked")){
        selectedTags+='@'+(allTags[1]);
    }
    if ($('#event_tag3').is(":checked")){
        selectedTags+='@'+(allTags[2]);
    }
    let detail = $("#event_detail").val();
    if (!detail || detail == ''){
        detail = "null detail";
    }
    const data = {'add_id': id, 'add_title':title,'add_date':date,'add_time':time,'add_tag':selectedTags,'add_detail':detail, 'token':token,
    'members':members};

    fetch("addEvent.php", {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })

    .then(function(data) {
        updateEventByHidden();
        alert(data.success ? `You've Added Event!${data.message}`: `Adding failed!${data.message}`);
    })
    .catch(err => console.error(err));
}
document.getElementById("add-event_btn").addEventListener("click", addEvent, false); 

let isThisMonth = -99;
let day = -999;
let eid = -999;
function showEvent() {
    let rawDate = $("#month_year").data('cleandate');
    rawDate = updateMonth(rawDate,isThisMonth);
    date = rawDate+ "-"+String(day).padStart(2, '0');
    $("#hidden-date").val(date);
    updateEvent(date);
    updateEventByHidden();
}

function updateEvent(date){
    let id = $('#hidden-id').val();
    let token = $('#hidden-token').val();
    const data = {'id' : id, 'date': date, 'token':token};
    fetch("showEvent.php", {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success){
            let eventParent = $("#event-total");
            eventParent.html("");
            if (data.sent_data.length>0){

                data.sent_data.forEach(function (item, index) {
                    let eid = item.eid;
                    let uid = item.uid;
                    let title = item.title;
                    let date = item.date;
                    let time = item.time;
                    let tags = item.tags;
                    let detail = item.detail;
                    let uniqueId = eid+"Z"+uid; 

                    let temp = "";
                    temp += "<li class='event-inst' id='"+ uniqueId +"'>";
                    temp += '<p hidden>' + eid+ '</p>';
                    temp += '<p hidden>' + uid+ '</p>';
                    temp += '<p>' + title + '</p>';
                    temp += '<p>' + date + '</p>';
                    temp += '<p>' + time + '</p>';
                    temp += '<p>' + tags + '</p>';
                    temp += '<p>' + detail + '</p>';
                    temp += "<button class = 'editBtn' id= " + "editBtn"+ uniqueId +  " > Edit </button>";
                    temp += "<button class = 'deleteBtn' id= " + "deleteBtn"+ uniqueId +  " > Delete </button>";
                    temp += "</li>";
                    eventParent.html(eventParent.html() + temp);
                    let editBtnid = 'editBtn'+uniqueId;
                    let deleteBtnid = 'deleteBtn'+uniqueId;
                    let btnSid = 'btnS'+uniqueId;

                    $(document).ready(function(){
                        $("#"+editBtnid).click(function() {
                            btnEdit(uniqueId);
                            let spiltArray = uniqueId.split("Z");
                            eid = spiltArray[0];
                            $("#hidden-eid").val(eid);
                        });
                        $("#"+deleteBtnid).click(function() {
                            btnDelete(uniqueId);
                            let spiltArray = uniqueId.split("Z");
                            let eid = spiltArray[0];
                            let uid = spiltArray[1];
                            $("#hidden-eid").val(eid);
                            deleteEvent(eid);
                        });
                    });   

                    function btnEdit(uniqueId){
                        let spiltArray = uniqueId.split("Z");
                        let eid = spiltArray[0];
                        let uid = spiltArray[1];
                    }

                    $('.editBtn').click(function () {
                        modalEdit.style.display = "block";
                        
                    })

                    function btnDelete(uniqueId){
                        let spiltArray = uniqueId.split("Z");
                        let eid = spiltArray[0];
                        let uid = spiltArray[1];   
                    }
                });
            }
        }
    })
    .catch(err => console.error(err));
}


$("#edit_btn").click(function() {   
    eid = $("#hidden-eid").val();                         
    editEvent(eid);
});


$("#share_btn").click(function() {
    let sid = $("#share_input").val();
    eid = $("#hidden-eid").val();  
    shareEvent(eid, sid);
})

function updateEventByHidden(){
    let hiddenDate = $('#hidden-date').val();
    updateEvent(hiddenDate);
}

function editEvent(eid) {
    let allTags = ["work","home","school"];
    let username = $('#hidden-username').val();
    let uid = $('#hidden-id').val();
    let token = $('#hidden-token').val();

    date = $("#edit_date").val();
    let title = $("#edit_title").val();
    if (!title || title == ''){
        title = "UNNAMED TITLE";
    }
    let time = $("#edit_time").val();
    let selectedTags = "";
    if ($('#edit_tag1').is(":checked")){
        selectedTags += '@'+(allTags[0]);
    }
    if ($('#edit_tag2').is(":checked")){
        selectedTags+='@'+(allTags[1]);
    }
    if ($('#edit_tag3').is(":checked")){
        selectedTags+='@'+(allTags[2]);
    }
    let detail = $("#edit_detail").val();
    if (!detail || detail == ''){
        detail = "UNNAMED DETAIL";
    }

    const newdata = {'event_id':eid, 'add_id':uid, 'add_title':title,'add_date':date,'add_time':time,'add_tag':selectedTags,'add_detail':detail, 'token':token};
    fetch("editEvent.php", {
        method: 'POST',
        body: JSON.stringify(newdata),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        updateEventByHidden();
        modalEdit.style.display = "block";
    })
    .catch(err => console.error(err));
}

function deleteEvent(eid) {
    let uid = $('#hidden-id').val();
    let token = $('#hidden-token').val();

    const ddata = {'event_id':eid, 'user_id':uid, 'token':token};
    fetch("deleteEvent.php", {
        method: 'POST',
        body: JSON.stringify(ddata),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })

    .then(function(data) {
        updateEventByHidden();
    })
    .catch(err => console.error(err));

}

function shareEvent(eid, sid) {
    let username = $('#hidden-username').val();
    let uid = $('#hidden-id').val();
    let token = $('#hidden-token').val();
    const sharedata = {'event_id':eid, 'user_id':uid, 'share_id':sid, 'token':token};

    fetch("shareEvent.php", {
        method: 'POST',
        body: JSON.stringify(sharedata),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })

    .then(function(data) {
        alert(data.success ? "You've shared Event!!" : `Sharing failed!${data.message}`);
    })
    .catch(err => console.error(err));
    updateEventByHidden();
}

function updateClickDate(){
    let currs = document.getElementsByClassName("curr");
    for (let i = 0; i < currs.length; i++) {
        currs[i].addEventListener("click", setZero, false);
        currs[i].addEventListener('click', function(){
            day = currs[i].textContent;
            showEvent();
        }, false);
    }
    let nexts = document.getElementsByClassName("next");
    for (let i = 0; i < nexts.length; i++) {
        nexts[i].addEventListener("click", setNext, false);
        nexts[i].addEventListener('click', function(){
            day = nexts[i].textContent;
            showEvent();
        }, false);
    }
    let prevs = document.getElementsByClassName("prev");
    for (let i = 0; i < prevs.length; i++) {
        prevs[i].addEventListener("click", setPrev, false);
        prevs[i].addEventListener('click', function(){
            day = prevs[i].textContent;
            showEvent();
        }, false);
    }

    function setNext(event) {
        isThisMonth = 1;
    }
    function setPrev(event) {
        isThisMonth = -1;
    }
    function setZero(event) {
        isThisMonth = 0;
    }
}

function updateMonth(rawDate, isThisMonth){
    
    const words = rawDate.split('-');
    let year = words[0];
    let mon = parseInt(words[1]);
    if (mon == 0 && isThisMonth == -1) mon = 12;
    else if (mon == 11 && isThisMonth == 1) mon = 1;
    else mon = mon+1+isThisMonth;
    rawDate = year+"-"+String(mon).padStart(2, '0');
    return rawDate;
}
updateClickDate();