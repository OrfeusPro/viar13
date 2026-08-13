// Select buttons

const rec = document.querySelector(".w-on");
const stop = document.querySelector(".w-off");
const audioPlay = document.querySelector(".audio");
const stopwatch = document.querySelector(".time");
const remove = document.querySelector(".r-audio_remove");

let typeOfMedia = {
  audio: true,
};
let chunks = [];
var options = {
  audioBitsPerSecond: 128000,
  videoBitsPerSecond: 2500000,
  mimeType: "audio/webm",
};
let counter = 0;

//

let recStream;

const recFunction = async () => {
  try {
    // Access to computer devices
    const mediaDevices = await navigator.mediaDevices.getUserMedia(typeOfMedia);
    if (mediaDevices.active === true) {
      recStream = new MediaRecorder(mediaDevices, options);
      recStream.ondataavailable = (e) => {
        chunks.push(e.data);
        if (recStream.state == "inactive") {
          let blob = new Blob(chunks, { type: "audio/webm" });
          createAudioElement(URL.createObjectURL(blob));
        }
      };
      recStream.start();
    }
  } catch (error) {
    if (error) console.log(error);
  }
};

function createAudioElement(blobUrl) {
  const divEl = document.createElement("div");
  // Assign it a class
  divEl.className = "div-audio";

  const audioEl = document.createElement("audio");
  audioEl.className = "audio";
  audioEl.controls = true;
  const sourceEl = document.createElement("source");
  sourceEl.src = blobUrl;
  sourceEl.type = "audio/webm";
  audioEl.appendChild(sourceEl);
  divEl.appendChild(audioEl);
  $(".r-audio_box").css("display", "flex").hide().fadeIn();
  $(".r-audio_inn").append(divEl);
}

rec.onclick = (e) => {
  if ($(".r-audio_inn").children().length > 0) {
    return;
  } else {
    rec.disabled = true;
    $(rec).closest('.input-item').find('.recording-animation').addClass('recording-block');
    $(rec).parent().addClass("active-rec");
    
    stop.disabled = false;
    recFunction();
    clearInterval(swInterval);
    swIternal = setInterval(stopwatchFunction, 1000);
  }
};
stop.onclick = (e) => {
  rec.disabled = false;
  $(rec).closest('.input-item').find('.recording-animation').removeClass('recording-block');
  $(rec).parent().removeClass("active-rec");
  stop.disabled = true;
  clearInterval(swIternal);
  sec = 0;
  min = 0;
  recStream.stop();
};
remove.onclick = () => {
  $('.div-audio').remove();
  $(".r-audio_box").fadeOut(0);
  stopwatch.innerHTML = "0:00";
};
let swInterval;
let displayStopwatch;
let sec = 0;
let min = 0;
let stopwatchFunction = () => {
  sec++;
  if (sec <= 9) {
    sec = "0" + sec;
  }
  if (sec === 60) {
    sec = 0;
    min++;
    if (min <= 9) {
      min = "0" + min;
    }
  }
  if (min === 60) {
    min = 0;
  }
  displayStopwatch = min + ":" + sec;
  stopwatch.innerHTML = displayStopwatch;
};
