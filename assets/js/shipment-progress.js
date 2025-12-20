const progress = document.getElementById("progress");
const progressSteps = document.querySelectorAll(".progress-step");
const progressActive = document.querySelectorAll(".progress-step-active");
const progressCompleted = document.querySelectorAll(".progress-step-completed");

function updateProgressbar() {
  if(progress !== null)
  {
    progress.style.width = ((progressCompleted.length) / (progressSteps.length - 1)) * 100 + "%";	
    if(progressSteps.length == progressCompleted.length)
    {
      console.log("end");
      progress.style.width = 100 +"%";
    }
  }
}

updateProgressbar();