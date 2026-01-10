<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Awwwards Level Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif}
body{
  height:100vh;display:flex;justify-content:center;align-items:center;
  background:#111;
}
.card{
  width:900px;height:500px;background:#f3f3f3;
  border-radius:22px;display:flex;overflow:hidden;
}

/* LEFT */
.left{flex:1;background:#e9e9e9;position:relative}

/* RIGHT */
.right{width:380px;background:#fff;padding:40px}
.right h2{margin-bottom:6px}
.right p{font-size:13px;color:#777;margin-bottom:20px}

input{
  width:100%;padding:12px;margin-bottom:14px;
  border-radius:8px;border:1px solid #ddd
}
button{
  width:100%;padding:12px;border:none;border-radius:10px;
  background:#111;color:#fff;cursor:pointer
}

/* CHARACTER */
.character{
  position:absolute;
  display:flex;
  flex-direction:column;
  align-items:center;
  transform-origin:center;
}

/* SHAPES */
.orange{width:180px;height:120px;background:#ff8c42;border-radius:90px 90px 20px 20px;bottom:80px;left:80px}
.purple{width:100px;height:180px;background:#6c2bd9;border-radius:20px;top:60px;left:200px}
.black{width:80px;height:140px;background:#111;border-radius:20px;bottom:90px;left:270px}
.yellow{width:90px;height:200px;background:#ffd43b;border-radius:20px;bottom:60px;right:80px}

/* FACE */
.eyes{display:flex;margin-top:18px}
.eye{
  width:18px;height:18px;background:#fff;border-radius:50%;
  margin:0 4px;overflow:hidden;position:relative
}
.pupil{
  width:8px;height:8px;background:#000;border-radius:50%;
  position:absolute;top:5px;left:5px
}
.lid{
  position:absolute;top:0;left:0;width:100%;height:0;
  background:#fff;
}

/* NOSE */
.nose{
  width:8px;height:10px;background:#000;border-radius:50%;
  margin-top:6px
}

/* MOUTH */
.mouth{
  width:24px;height:10px;
  border-bottom:3px solid #000;
  border-radius:0 0 20px 20px;
  margin-top:6px;
  transition:.3s;
}
.mouth.sad{
  border-bottom:none;
  border-top:3px solid #000;
  border-radius:20px 20px 0 0;
}
</style>
</head>

<body>

<div class="card" id="card">
  <div class="left">

    @foreach(['orange','purple','black','yellow'] as $c)
    <div class="character {{ $c }}">
      <div class="eyes">
        <div class="eye"><div class="lid"></div><div class="pupil"></div></div>
        <div class="eye"><div class="lid"></div><div class="pupil"></div></div>
      </div>
      <div class="nose"></div>
      <div class="mouth"></div>
    </div>
    @endforeach

  </div>

  <div class="right">
    <h2>Welcome back!</h2>
    <p>Please enter your details</p>

    <form onsubmit="event.preventDefault(); animatedLogin();">
      <input type="email" placeholder="Email" required>
      <input type="password" id="password" placeholder="Password" required>
      <button>Log In</button>
    </form>
  </div>
</div>

<script>
const pupils=document.querySelectorAll(".pupil");
const noses=document.querySelectorAll(".nose");
const mouths=document.querySelectorAll(".mouth");
const characters=document.querySelectorAll(".character");
const lids=document.querySelectorAll(".lid");
const password=document.getElementById("password");
const card=document.getElementById("card");

/* 👀 CURSOR FOLLOW */
document.addEventListener("mousemove",e=>{
  pupils.forEach(p=>{
    const r=p.getBoundingClientRect();
    const a=Math.atan2(e.clientY-r.top,e.clientX-r.left);
    gsap.to(p,{x:Math.cos(a)*4,y:Math.sin(a)*4,duration:.1});
  });

  noses.forEach(n=>{
    gsap.to(n,{x:(e.clientX/window.innerWidth-.5)*6,y:(e.clientY/window.innerHeight-.5)*6,duration:.2});
  });

  mouths.forEach(m=>{
    gsap.to(m,{x:(e.clientX/window.innerWidth-.5)*6,rotation:(e.clientX/window.innerWidth-.5)*8,duration:.2});
  });
});

/* 👀 RANDOM BLINK */
setInterval(()=>{
  gsap.to(lids,{height:18,duration:.08,yoyo:true,repeat:1});
},Math.random()*4000+3000);

/* 🫣 PASSWORD FOCUS */
password.addEventListener("focus",()=>{
  gsap.to(characters,{rotation:-6,y:6,stagger:.03,duration:.3});
  gsap.to(pupils,{y:6,duration:.2});
});

/* RESET FACE */
password.addEventListener("blur",()=>{
  gsap.to(characters,{rotation:0,y:0,stagger:.03});
});

/* LOGIN RESULT */
function animatedLogin(){
  if(password.value==="1234"){
    mouths.forEach(m=>m.classList.remove("sad"));
    gsap.to(characters,{y:-10,stagger:.05,yoyo:true,repeat:1});
  }else{
    mouths.forEach(m=>m.classList.add("sad"));
    gsap.fromTo(card,{x:-8},{x:8,repeat:4,yoyo:true,duration:.08});
  }
}
</script>
	<script>
function animatedLogin(){
  if(password.value === "1234"){

    // 😊 HAPPY
    mouths.forEach(m=>m.classList.remove("sad"));

    // 🚀 FLY UP ANIMATION
    gsap.to(characters,{
      y:-300,
      rotation:()=>gsap.utils.random(-20,20),
      scale:1.1,
      opacity:0,
      duration:1.2,
      ease:"power3.inOut",
      stagger:0.08,
      onComplete:()=>{
        // reset after fly
        gsap.set(characters,{
          y:0,rotation:0,scale:1,opacity:1
        });
      }
    });

  }else{

    // 😢 SAD
    mouths.forEach(m=>m.classList.add("sad"));

    // 💥 FLY SHAKE + RETURN
    gsap.timeline()
      .to(characters,{
        x:()=>gsap.utils.random(-40,40),
        y:()=>gsap.utils.random(-30,30),
        rotation:()=>gsap.utils.random(-15,15),
        duration:.15,
        stagger:.03,
        repeat:3,
        yoyo:true
      })
      .to(card,{
        x:-8,
        repeat:4,
        yoyo:true,
        duration:.08
      });
  }
}
</script>


</body>
</html>
