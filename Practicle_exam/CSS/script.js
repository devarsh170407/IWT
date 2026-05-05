document.getElementById("taskForm").addEventListener("submit", function(e){

    let title = document.getElementById("title").value;
    let priority = document.getElementById("priority").value;
    let date = document.getElementById("dueDate").value;

    let valid = true;

    if(title.length < 3){
        document.querySelector("#title + .error").innerText = "Min 3 characters";
        valid = false;
    }

    if(priority === ""){
        document.querySelector("#priority + .error").innerText = "Select priority";
        valid = false;
    }

    let today = new Date().toISOString().split("T")[0];
    if(date < today){
        document.querySelector("#dueDate + .error").innerText = "Invalid date";
        valid = false;
    }

    if(!valid){
        e.preventDefault();
        alert("fix errors!");
    }
});




$("#resetBtn").click(function(){

    if(confirm("Clear form?")){

        $("#taskForm")[0].reset();

        $(".result-section").fadeOut(500).fadeIn(500);
        $(".task-list-section").fadeOut(500).fadeIn(500);

        alert("Form cleared");
    }
});

$("#result").hover(
    function(){ $(this).css("background","#d1ecf1"); },
    function(){ $(this).css("background","#e3f2fd"); }
);