let currentMonth = new Month(2022, 9);

document.getElementById("next_month_btn").addEventListener("click", function(event){
        currentMonth = currentMonth.nextMonth();
        updateCalendar(); 
        updateClickDate();
}, false);

document.getElementById("prev_month_btn").addEventListener("click", function(event){
	    currentMonth = currentMonth.prevMonth();
        updateCalendar();
        updateClickDate();
}, false);

function updateCalendar(){

    let months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let monYear = document.getElementById("month_year");
    $('#month_year').data('cleandate',currentMonth.year+"-"+String(currentMonth.month).padStart(2, '0'));
    monYear.innerHTML = months[parseInt(currentMonth.month)] + " " + currentMonth.year;
	let weeks = currentMonth.getWeeks();
    let dateParent = document.getElementsByClassName("card-body-dates")[0];
    dateParent.innerHTML = [];


    let reachThisMonth = false;
    let isNextMonth = false;
	for(let w in weeks){
		let days = weeks[w].getDates();

		for(let d in days){
            let cleanDay = parseInt((days[d].toISOString().substring(8,10)));
            let preFixGray = "<li class='prev'>";
            const preFix = "<li class='curr'>";

            if (cleanDay == 1) {
                reachThisMonth = !reachThisMonth;
                if (!reachThisMonth){
                    isNextMonth = true;
                }
            }
            if (isNextMonth){
                preFixGray = "<li class='next'>";
            }
            if (reachThisMonth) {
                dateParent.innerHTML += preFix +  "<a href='#' class = 'display-date'>"+cleanDay+"</a>" +"</li>" ;
            } else {
                dateParent.innerHTML += preFixGray+  "<a href='#' class = 'display-date'>"+cleanDay +"</a>"+"</li>" ;
            }
		}
	}
}
function updateTimeDetail(){
    let timeP = document.getElementById("cur_time");
    let today = new Date();
    let time = String(today.getHours()).padStart(2, '0') + ":" +String(today.getMinutes()).padStart(2, '0');
    timeP.innerHTML = time;
}

updateTimeDetail();

