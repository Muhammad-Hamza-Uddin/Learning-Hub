<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] === 'admin'){
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Courses — LearnHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --teal:   #2D5F5D;
      --coral:  #E07A5F;
      --amber:  #F4A261;
      --dark:   #181818;
      --cream:  #FDF8F3;
      --white:  #FFFFFF;
      --muted:  #6B6B6B;
      --border: #EBEBEB;
      --shadow: 0 8px 40px rgba(0,0,0,.09);
      --shadow-lg: 0 24px 80px rgba(0,0,0,.18);
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--dark);
      overflow-x: hidden;
    }

    /* ── AMBIENT BG ── */
    .bg-orbs {
      position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
    }
    .bg-orbs span {
      position: absolute; border-radius: 50%; opacity: .035;
      animation: drift 22s ease-in-out infinite;
    }
    .bg-orbs span:nth-child(1){ width:680px;height:680px;background:var(--teal);  top:-220px;right:-200px; animation-delay:0s;}
    .bg-orbs span:nth-child(2){ width:500px;height:500px;background:var(--coral); bottom:-160px;left:-160px; animation-delay:8s;}
    .bg-orbs span:nth-child(3){ width:360px;height:360px;background:var(--amber); top:55%;left:55%; animation-delay:16s;}
    @keyframes drift {
      0%,100%{ transform:translate(0,0) scale(1);}
      40%{ transform:translate(60px,-60px) scale(1.08);}
      70%{ transform:translate(-40px,40px) scale(.94);}
    }

    /* ── HEADER ── */
    header {
      position: sticky; top: 0; z-index: 200;
      padding: 1.4rem 5%;
      display: flex; justify-content: space-between; align-items: center;
      background: rgba(253,248,243,.92);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(0,0,0,.06);
      animation: slideDown .7s ease-out both;
    }
    @keyframes slideDown { from{opacity:0;transform:translateY(-28px)} to{opacity:1;transform:translateY(0)} }

    .logo {
      font-family: 'Crimson Pro', serif;
      font-size: 1.9rem; font-weight: 700;
      color: var(--teal); text-decoration: none; letter-spacing:-.02em;
    }
    nav { display:flex; gap:2.5rem; align-items:center; }
    nav a {
      text-decoration:none; color:var(--dark); font-weight:500; font-size:.97rem;
      position:relative; transition:color .25s;
    }
    nav a::after {
      content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px;
      background:var(--coral); transition:width .3s;
    }
    nav a:hover { color:var(--teal); }
    nav a:hover::after, nav a.active::after { width:100%; }
    nav a.active { color:var(--teal); }
    .nav-cta {
      background:var(--teal); color:var(--white) !important;
      padding:.65rem 1.4rem; border-radius:50px; transition:all .3s;
    }
    .nav-cta::after { display:none; }
    .nav-cta:hover { background:var(--coral); transform:translateY(-2px); box-shadow:0 10px 28px rgba(224,122,95,.35); }

    /* ── PAGE HERO ── */
    .page-hero {
      position:relative; z-index:10;
      padding: 5rem 5% 2.5rem; text-align:center;
      animation: riseUp .85s ease-out .2s both;
    }
    @keyframes riseUp { from{opacity:0;transform:translateY(36px)} to{opacity:1;transform:translateY(0)} }

    .page-hero h1 {
      font-family:'Crimson Pro',serif;
      font-size: clamp(2.6rem,5vw,4.2rem);
      font-weight:700; color:var(--teal);
      letter-spacing:-.03em; line-height:1.1; margin-bottom:1rem;
    }
    .page-hero h1 em { color:var(--coral); font-style:normal; }
    .page-hero p { font-size:1.15rem; color:var(--muted); max-width:640px; margin:0 auto; line-height:1.75; }

    /* ── SEARCH + FILTERS ── */
    .controls {
      position:relative; z-index:10;
      padding: 2rem 5% 2.5rem;
      animation: riseUp .85s ease-out .35s both;
    }
    .search-wrap {
      max-width:680px; margin:0 auto 1.8rem; position:relative;
    }
    .search-wrap input {
      width:100%; padding:1.05rem 3.5rem 1.05rem 1.5rem;
      border:2px solid var(--border); border-radius:50px;
      font-family:'DM Sans',sans-serif; font-size:1rem;
      background:var(--white); transition:all .3s;
    }
    .search-wrap input:focus {
      outline:none; border-color:var(--teal);
      box-shadow:0 8px 32px rgba(45,95,93,.12);
    }
    .search-wrap .ico { position:absolute; right:1.4rem; top:50%; transform:translateY(-50%); font-size:1.3rem; pointer-events:none; }

    .filters { display:flex; gap:.75rem; flex-wrap:wrap; justify-content:center; }
    .pill {
      padding:.6rem 1.35rem; border:2px solid var(--border); background:var(--white);
      border-radius:50px; font-family:'DM Sans',sans-serif; font-weight:600;
      font-size:.88rem; cursor:pointer; transition:all .28s; color:var(--dark);
    }
    .pill:hover { border-color:var(--teal); color:var(--teal); transform:translateY(-2px); }
    .pill.active { background:var(--teal); border-color:var(--teal); color:var(--white); }

    /* ── COURSES GRID ── */
    .courses-section { position:relative; z-index:10; padding:0 5% 6rem; }

    .toolbar {
      display:flex; justify-content:space-between; align-items:center; margin-bottom:1.8rem;
    }
    .toolbar span { color:var(--muted); font-size:.95rem; }
    .toolbar span strong { color:var(--dark); }
    .sort-sel {
      padding:.6rem 1.3rem; border:2px solid var(--border); border-radius:50px;
      font-family:'DM Sans',sans-serif; font-weight:600; font-size:.88rem;
      background:var(--white); cursor:pointer; transition:border-color .25s;
    }
    .sort-sel:focus { outline:none; border-color:var(--teal); }

    .grid {
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px,1fr));
      gap:2.2rem;
    }

    /* ── CARD ── */
    .card {
      background:var(--white); border-radius:22px;
      overflow:hidden; box-shadow:var(--shadow);
      transition:transform .38s cubic-bezier(.22,1,.36,1), box-shadow .38s;
      animation: cardIn .65s ease-out both;
    }
    .card:nth-child(1){animation-delay:.08s}
    .card:nth-child(2){animation-delay:.16s}
    .card:nth-child(3){animation-delay:.24s}
    .card:nth-child(4){animation-delay:.32s}
    .card:nth-child(5){animation-delay:.40s}
    .card:nth-child(6){animation-delay:.48s}
    .card:nth-child(7){animation-delay:.56s}
    .card:nth-child(8){animation-delay:.64s}
    .card:nth-child(9){animation-delay:.72s}
    @keyframes cardIn { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-lg);
    }

    /* ── THUMBNAIL ── */
    .thumb {
      position:relative; width:100%; padding-bottom:56.25%; /* 16:9 */
      overflow:hidden; cursor:pointer; background:#111;
    }
    .thumb img {
      position:absolute; inset:0; width:100%; height:100%;
      object-fit:cover; transition:transform .5s ease, filter .4s ease;
      filter:brightness(.92);
    }
    .card:hover .thumb img { transform:scale(1.06); filter:brightness(.8); }

    /* play button overlay */
    .thumb .play-btn {
      position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
      z-index:5;
    }
    .thumb .play-btn .circle {
      width:68px; height:68px; border-radius:50%;
      background:rgba(255,255,255,.92);
      display:flex; align-items:center; justify-content:center;
      transition:transform .3s, box-shadow .3s, background .3s;
      box-shadow:0 6px 30px rgba(0,0,0,.35);
    }
    .thumb .play-btn .circle svg {
      width:28px; height:28px; fill:var(--teal); margin-left:4px;
    }
    .card:hover .thumb .play-btn .circle {
      transform:scale(1.18);
      background:var(--coral);
      box-shadow:0 10px 40px rgba(224,122,95,.5);
    }
    .card:hover .thumb .play-btn .circle svg { fill:#fff; }

    /* duration chip */
    .thumb .duration {
      position:absolute; bottom:.75rem; right:.75rem;
      background:rgba(0,0,0,.7); color:#fff;
      font-size:.78rem; font-weight:600;
      padding:.25rem .65rem; border-radius:8px;
    }

    /* badges */
    .thumb .badge {
      position:absolute; top:.75rem; left:.75rem;
      background:rgba(255,255,255,.95); color:var(--teal);
      font-size:.78rem; font-weight:700; padding:.3rem .8rem; border-radius:20px;
      text-transform:uppercase; letter-spacing:.04em;
    }
    .thumb .badge.hot   { background:var(--coral); color:#fff; }
    .thumb .badge.new   { background:var(--teal);  color:#fff; }
    .thumb .badge.free  { background:#22c55e;       color:#fff; }

    .thumb .level {
      position:absolute; top:.75rem; right:.75rem;
      background:rgba(255,255,255,.95);
      font-size:.78rem; font-weight:700; padding:.3rem .8rem; border-radius:20px;
    }
    .level.beginner     { color:#22c55e; }
    .level.intermediate { color:#f59e0b; }
    .level.advanced     { color:#ef4444; }

    /* ── CARD BODY ── */
    .card-body { padding:1.4rem 1.5rem; }

    .cat {
      display:inline-block; color:var(--coral);
      font-size:.8rem; font-weight:700; text-transform:uppercase;
      letter-spacing:.06em; margin-bottom:.5rem;
    }

    .card-title {
      font-family:'Crimson Pro',serif;
      font-size:1.42rem; font-weight:700;
      color:var(--teal); line-height:1.25; margin-bottom:.65rem;
    }

    .card-desc {
      font-size:.9rem; color:var(--muted); line-height:1.65; margin-bottom:1.1rem;
    }

    .instructor {
      display:flex; align-items:center; gap:.65rem; margin-bottom:1rem;
    }
    .avatar {
      width:34px; height:34px; border-radius:50%;
      background:linear-gradient(135deg,var(--teal),var(--coral));
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-weight:700; font-size:.82rem; flex-shrink:0;
    }
    .instructor-name { font-size:.88rem; color:var(--muted); }

    .card-footer {
      display:flex; justify-content:space-between; align-items:center;
      padding-top:1rem; border-top:1px solid var(--border);
    }
    .meta { display:flex; gap:1.2rem; font-size:.82rem; color:#aaa; }
    .meta span { display:flex; align-items:center; gap:.3rem; }
    .price {
      font-family:'Crimson Pro',serif;
      font-size:1.7rem; font-weight:700; color:var(--teal);
    }
    .price.free { color:#22c55e; }

    /* ── VIDEO MODAL ── */
    .modal-overlay {
      position:fixed; inset:0; z-index:1000;
      background:rgba(10,10,10,.85);
      backdrop-filter:blur(8px);
      display:flex; align-items:center; justify-content:center;
      opacity:0; pointer-events:none;
      transition:opacity .35s ease;
    }
    .modal-overlay.open {
      opacity:1; pointer-events:all;
    }

    .modal-box {
      position:relative;
      width:min(900px, 94vw);
      background:var(--dark);
      border-radius:24px;
      overflow:hidden;
      box-shadow:0 40px 120px rgba(0,0,0,.7);
      transform:scale(.88) translateY(40px);
      transition:transform .4s cubic-bezier(.22,1,.36,1);
    }
    .modal-overlay.open .modal-box {
      transform:scale(1) translateY(0);
    }

    .modal-video-wrap {
      position:relative; width:100%; padding-bottom:56.25%;
      background:#000;
    }
    .modal-video-wrap iframe {
      position:absolute; inset:0; width:100%; height:100%; border:none;
    }

    .modal-info {
      padding:1.5rem 2rem 2rem;
      display:flex; justify-content:space-between; align-items:flex-start; gap:1.5rem;
    }
    .modal-info-left {}
    .modal-info-left .modal-cat {
      font-size:.78rem; font-weight:700; text-transform:uppercase;
      letter-spacing:.06em; color:var(--coral); margin-bottom:.4rem;
    }
    .modal-info-left h2 {
      font-family:'Crimson Pro',serif;
      font-size:1.6rem; font-weight:700; color:#fff; margin-bottom:.5rem; line-height:1.2;
    }
    .modal-info-left p { font-size:.9rem; color:#bbb; line-height:1.6; }

    .modal-close {
      position:absolute; top:1rem; right:1rem; z-index:10;
      width:42px; height:42px; border-radius:50%;
      background:rgba(255,255,255,.12); border:none; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      transition:background .25s, transform .25s; color:#fff; font-size:1.3rem;
    }
    .modal-close:hover { background:var(--coral); transform:scale(1.1); }

    .enroll-btn {
      flex-shrink:0;
      padding:.9rem 1.8rem; background:var(--teal); color:#fff;
      border:none; border-radius:50px; font-family:'DM Sans',sans-serif;
      font-weight:700; font-size:.95rem; cursor:pointer; transition:all .3s;
      white-space:nowrap;
    }
    .enroll-btn:hover { background:var(--coral); transform:translateY(-2px); box-shadow:0 12px 30px rgba(224,122,95,.4); }

    /* ── LOAD MORE ── */
    .load-more {
      text-align:center; margin-top:3.5rem;
    }
    .load-btn {
      padding:1rem 3.5rem; background:var(--teal); color:#fff; border:none;
      border-radius:50px; font-family:'DM Sans',sans-serif;
      font-weight:700; font-size:1rem; cursor:pointer; transition:all .3s;
    }
    .load-btn:hover { background:var(--coral); transform:translateY(-3px); box-shadow:0 14px 38px rgba(224,122,95,.42); }

    /* ── FOOTER ── */
    footer {
      background:var(--dark); color:#fff;
      padding:4rem 5% 2rem; position:relative; z-index:10;
    }
    .footer-grid {
      display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:4rem; margin-bottom:3rem;
    }
    .footer-brand h3 { font-family:'Crimson Pro',serif; font-size:2rem; margin-bottom:.9rem; }
    .footer-brand p  { color:#999; line-height:1.7; }
    .footer-col h4   { margin-bottom:1.2rem; font-weight:600; }
    .footer-col ul   { list-style:none; }
    .footer-col li   { margin-bottom:.7rem; }
    .footer-col a    { color:#999; text-decoration:none; transition:color .25s; }
    .footer-col a:hover { color:var(--amber); }
    .footer-bottom {
      border-top:1px solid #2f2f2f; padding-top:1.8rem;
      text-align:center; color:#555; font-size:.9rem;
    }

    /* ── RESPONSIVE ── */
    @media(max-width:900px){
      .grid { grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); }
      .footer-grid { grid-template-columns:1fr 1fr; }
    }
    @media(max-width:640px){
      header { flex-direction:column; gap:1rem; }
      nav    { flex-wrap:wrap; justify-content:center; gap:1rem; }
      .grid  { grid-template-columns:1fr; }
      .footer-grid { grid-template-columns:1fr; gap:2rem; }
      .modal-info { flex-direction:column; }
    }
  .historyBtn{
background:#ff4b2b;
color:white;
border:none;
padding:8px 14px;
border-radius:6px;
cursor:pointer;
margin-left:10px;
}
.history-grid{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
gap:16px;
margin-top:16px;
}

.history-card{
background:white;
border-radius:10px;
overflow:hidden;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
cursor:pointer;
transition:transform .2s, box-shadow .2s;
}
.history-card:hover{
transform:translateY(-4px);
box-shadow:0 8px 22px rgba(0,0,0,0.18);
}

/* thumbnail wrapper with play button */
.hc-thumb{
position:relative;
width:100%;
padding-top:56.25%;
overflow:hidden;
background:#0C2554;
}
.hc-thumb img{
position:absolute;
inset:0;width:100%;height:100%;object-fit:cover;
}
.hc-play{
position:absolute;
inset:0;display:flex;align-items:center;justify-content:center;
background:rgba(7,19,48,0.45);
color:#F0C060;font-size:1.6rem;
opacity:0;transition:opacity .2s;
}
.history-card:hover .hc-play{ opacity:1; }

/* card text */
.hc-info{ padding:10px 12px 12px; }
.hc-title{
font-size:.85rem;font-weight:700;color:#0C2554;
margin:0 0 4px;
line-height:1.35;
display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.hc-date{ font-size:.75rem; color:#8090a8; }

/* backward compat — old cards from server-side */
.history-card img{ width:100%; display:block; }
.history-card p{ font-size:.85rem;padding:8px 10px 4px;color:#334;font-weight:600;margin:0; }
#historyBox{
position:fixed;
top:80px;
right:20px;
width:350px;
max-height:500px;
overflow-y:auto;
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 10px 30px rgba(0,0,0,0.2);
z-index:999;
display:none;
}
  
/* ══ QUIZ SYSTEM ══ */
#quizOverlay {
  display:none; position:fixed; inset:0; z-index:9999;
  background:rgba(10,10,10,.92); backdrop-filter:blur(8px);
  align-items:center; justify-content:center; padding:1rem;
}
#quizOverlay.open { display:flex; }
.quiz-box {
  background:#1a1a2e; border-radius:20px; padding:2.5rem;
  max-width:680px; width:100%; max-height:90vh; overflow-y:auto;
  box-shadow:0 30px 80px rgba(0,0,0,.6);
  border:1px solid rgba(255,255,255,.08);
  animation: quizIn .4s cubic-bezier(.22,1,.36,1);
}
@keyframes quizIn { from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:translateY(0)} }
@keyframes dot-bounce { 0%,80%,100%{transform:scale(0)} 40%{transform:scale(1)} }
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.08)} }
.quiz-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
.quiz-header h2 { color:#fff; font-size:1.3rem; margin:0; }
.quiz-progress { font-size:.85rem; color:#aaa; }
.progress-bar-wrap { background:#333; border-radius:50px; height:6px; margin-bottom:2rem; }
.progress-bar-fill { background:linear-gradient(90deg,#667eea,#764ba2); height:6px; border-radius:50px; transition:width .4s; }
.quiz-question { color:#e8e8e8; font-size:1.05rem; font-weight:600; margin-bottom:1.2rem; line-height:1.6; }
.quiz-options { display:flex; flex-direction:column; gap:.75rem; margin-bottom:1.5rem; }
.quiz-option {
  background:rgba(255,255,255,.06); border:2px solid rgba(255,255,255,.1);
  border-radius:12px; padding:1rem 1.2rem; color:#ddd; cursor:pointer;
  transition:all .25s; font-size:.95rem; text-align:left;
}
.quiz-option:hover { background:rgba(102,126,234,.2); border-color:#667eea; color:#fff; }
.quiz-option.correct { background:rgba(34,197,94,.15); border-color:#22c55e; color:#22c55e; }
.quiz-option.wrong   { background:rgba(239,68,68,.15);  border-color:#ef4444;  color:#ef4444; }
.quiz-option.disabled { pointer-events:none; }
.quiz-feedback { padding:.9rem 1.1rem; border-radius:10px; margin-bottom:1.2rem; font-size:.9rem; font-weight:600; display:none; }
.quiz-feedback.correct { background:rgba(34,197,94,.15); color:#22c55e; border:1px solid rgba(34,197,94,.3); }
.quiz-feedback.wrong   { background:rgba(239,68,68,.15);  color:#ef4444;  border:1px solid rgba(239,68,68,.3); }
.quiz-nav { display:flex; justify-content:flex-end; }
.btn-next-q {
  background:linear-gradient(135deg,#667eea,#764ba2); color:#fff;
  border:none; padding:.85rem 2rem; border-radius:50px; font-size:.95rem;
  font-weight:700; cursor:pointer; transition:all .3s; display:none;
}
.btn-next-q:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(102,126,234,.4); }

/* ── RESULT SCREEN ── */
.quiz-result { text-align:center; }
.result-emoji { font-size:5rem; margin-bottom:1rem; }
.result-score { font-size:3rem; font-weight:800; margin:.5rem 0; }
.result-score.pass { color:#22c55e; }
.result-score.fail { color:#ef4444; }
.result-msg { color:#bbb; font-size:1rem; margin-bottom:2rem; line-height:1.6; }
.result-btns { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
.btn-cert {
  background:linear-gradient(135deg,#f093fb,#f5576c); color:#fff;
  border:none; padding:1rem 2rem; border-radius:50px; font-size:1rem;
  font-weight:700; cursor:pointer; transition:all .3s;
}
.btn-cert:hover { transform:translateY(-3px); box-shadow:0 15px 40px rgba(240,147,251,.4); }
.btn-retry {
  background:rgba(255,255,255,.1); color:#fff; border:2px solid rgba(255,255,255,.2);
  padding:1rem 2rem; border-radius:50px; font-size:1rem; font-weight:700; cursor:pointer; transition:all .3s;
}
.btn-retry:hover { background:rgba(255,255,255,.18); }
.btn-rewatch {
  background:rgba(255,165,0,.15); color:#ffa500; border:2px solid rgba(255,165,0,.3);
  padding:1rem 2rem; border-radius:50px; font-size:1rem; font-weight:700; cursor:pointer; transition:all .3s;
}
.btn-rewatch:hover { background:rgba(255,165,0,.25); }

/* ── TAKE TEST BUTTON ── */
.take-test-btn {
  background:linear-gradient(135deg,#667eea,#764ba2); color:#fff;
  border:none; padding:.75rem 1.8rem; border-radius:50px; font-size:.9rem;
  font-weight:700; cursor:pointer; margin-top:.8rem; transition:all .3s; display:none;
}
.take-test-btn:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(102,126,234,.4); }
.take-test-btn.visible { display:inline-block; }

/* ── CERTIFICATE ── */
#certOverlay {
  display:none; position:fixed; inset:0; z-index:10000;
  background:rgba(0,0,0,.95); align-items:center; justify-content:center; padding:1rem;
}
#certOverlay.open { display:flex; flex-direction:column; gap:1rem; }
#certCanvas { border-radius:12px; max-width:100%; box-shadow:0 20px 60px rgba(0,0,0,.8); }
.cert-actions { display:flex; gap:1rem; }
.btn-download-cert {
  background:linear-gradient(135deg,#f093fb,#f5576c); color:#fff;
  border:none; padding:.85rem 2rem; border-radius:50px; font-size:.95rem;
  font-weight:700; cursor:pointer;
}
.btn-close-cert {
  background:rgba(255,255,255,.1); color:#fff; border:2px solid rgba(255,255,255,.2);
  padding:.85rem 2rem; border-radius:50px; font-size:.95rem; font-weight:700; cursor:pointer;
}

</style>

</head>
<body>

<header>

  <a class="logo" href="index.html">LearnHub</a>
  <nav>
    <a href="courses.php" class="active">Courses</a>
    <a href="About.html">About</a>
    <a href="student-feedback.html">Student Feedback</a>
    <a href="Contact.html">Contact</a>
   <a href="logout.php" class="nav-cta">Logout</a>
    <button class="historyBtn" onclick="showHistory()">History</button>
    
  </nav>
</header>

<section class="page-hero">
  <h1>Explore <em>Expert-Crafted</em> Courses</h1>
  <p>Click any video preview to watch it instantly — then enroll and start your learning journey today.</p>
</section>

<section class="courses-section">
  <div class="grid" id="courseGrid">

    <?php
    // ── Default hardcoded courses (video_id as key to avoid duplicates) ──
    $default_courses = [
        "4sosXZsdy-s" => ["vid"=>"4sosXZsdy-s","title"=>"Bootstrap 5 Full Course","cat"=>"Web Design","desc"=>"Learn Bootstrap 5 and build fully responsive modern websites.","level"=>"Beginner","duration"=>"15h 10m","instructor"=>"Code Academy"],
        "PkZNo7MFNFg" => ["vid"=>"PkZNo7MFNFg","title"=>"Git & GitHub Complete Guide","cat"=>"Development Tools","desc"=>"Master version control and collaborate like a pro.","level"=>"Beginner","duration"=>"12h 45m","instructor"=>"James Carter"],
        "rfscVS0vtbw" => ["vid"=>"rfscVS0vtbw","title"=>"Python for Data Science & Machine Learning","cat"=>"Data Science","desc"=>"Learn data analysis, visualization, ML with Python.","level"=>"Intermediate","duration"=>"38h 15m","instructor"=>"Sarah Kim"],
        "kqtD5dpn9C8" => ["vid"=>"kqtD5dpn9C8","title"=>"Full PHP & MySQL Course","cat"=>"Backend Development","desc"=>"Learn PHP with real-world projects and database integration.","level"=>"Intermediate","duration"=>"35h 20m","instructor"=>"Daniel Scott"],
        "ua-CiDNNj30" => ["vid"=>"ua-CiDNNj30","title"=>"React JS Complete Guide","cat"=>"Frontend Development","desc"=>"Build powerful front-end apps using React.","level"=>"Advanced","duration"=>"42h 10m","instructor"=>"Emily Carter"],
        "LHBE6Q9XlzI" => ["vid"=>"LHBE6Q9XlzI","title"=>"Machine Learning A-Z","cat"=>"Artificial Intelligence","desc"=>"Master machine learning algorithms step by step.","level"=>"Advanced","duration"=>"55h 00m","instructor"=>"Dr. Andrew Miles"],
        "Z1Yd7upQsXY" => ["vid"=>"Z1Yd7upQsXY","title"=>"UI/UX Design Fundamentals","cat"=>"Design","desc"=>"Learn modern UI/UX principles and Figma basics.","level"=>"Beginner","duration"=>"22h 15m","instructor"=>"Sophia Adams"],
        "sBws8MSXN7A" => ["vid"=>"sBws8MSXN7A","title"=>"HTML & CSS Complete Guide","cat"=>"Web Development","desc"=>"Learn HTML5 and CSS3 from scratch with real projects.","level"=>"Beginner","duration"=>"18h 30m","instructor"=>"Chris Morgan"],
        "UB1O30fR-EE" => ["vid"=>"UB1O30fR-EE","title"=>"Advanced JavaScript Projects","cat"=>"Web Development","desc"=>"Build real-world JavaScript applications step by step.","level"=>"Advanced","duration"=>"31h 45m","instructor"=>"Alex Turner"],
        "Oe421EPjeBE" => ["vid"=>"Oe421EPjeBE","title"=>"Node.js & Express Masterclass","cat"=>"Backend Development","desc"=>"Build REST APIs and full backend systems using Node.js.","level"=>"Intermediate","duration"=>"36h 20m","instructor"=>"Ryan Mitchell"],
        "7eh4d6sabA0" => ["vid"=>"7eh4d6sabA0","title"=>"MySQL Database Bootcamp","cat"=>"Database","desc"=>"Master SQL queries, joins, indexes and optimization.","level"=>"Beginner","duration"=>"24h 10m","instructor"=>"Laura Smith"],
        "8hly31xKli0" => ["vid"=>"8hly31xKli0","title"=>"React Native Mobile Apps","cat"=>"Mobile Development","desc"=>"Create Android & iOS apps using React Native.","level"=>"Intermediate","duration"=>"40h 00m","instructor"=>"Kevin Brooks"],
        "pQN-pnXPaVg" => ["vid"=>"pQN-pnXPaVg","title"=>"Python Automation & Scripting","cat"=>"Programming","desc"=>"Automate tasks and build useful Python tools.","level"=>"Beginner","duration"=>"26h 50m","instructor"=>"David Clark"],
        "tPYj3fFJGjk" => ["vid"=>"tPYj3fFJGjk","title"=>"Cyber Security Fundamentals","cat"=>"Cyber Security","desc"=>"Learn ethical hacking basics and security concepts.","level"=>"Intermediate","duration"=>"33h 25m","instructor"=>"Ahmed Khan"],
        "aircAruvnKk" => ["vid"=>"aircAruvnKk","title"=>"Deep Learning with TensorFlow","cat"=>"Artificial Intelligence","desc"=>"Build neural networks and deep learning models.","level"=>"Advanced","duration"=>"48h 15m","instructor"=>"Dr. Sarah Lee"],
    ];

    // ── Fetch DB courses uploaded by admin ──
    $db_courses = [];
    $db_result = $conn->query("SELECT * FROM courses ORDER BY id DESC");
    if($db_result && $db_result->num_rows > 0){
        while($row = $db_result->fetch_assoc()){
            $db_courses[$row['video_id']] = [
                "vid"        => $row['video_id'],
                "title"      => $row['title'],
                "cat"        => $row['category'] ?? 'General',
                "desc"        => $row['description'] ?? '',
                "level"      => $row['level'] ?? 'Beginner',
                "duration"   => $row['duration'] ?? '',
                "instructor" => $row['instructor'] ?? 'Instructor',
            ];
        }
    }

    // ── Merge: DB courses override defaults, new ones added at top ──
    // DB courses come first (newest uploads on top), then defaults not already in DB
    $courses = $db_courses;
    foreach($default_courses as $vid => $course){
        if(!isset($courses[$vid])){
            $courses[$vid] = $course;
        }
    }

    foreach($courses as $course) {
        $thumb = "https://img.youtube.com/vi/" . $course['vid'] . "/maxresdefault.jpg";
        echo '<article class="card" data-vid="'.$course['vid'].'" data-title="'.htmlspecialchars($course['title']).'" data-cat="'.htmlspecialchars($course['cat']).'" data-desc="'.htmlspecialchars($course['desc']).'">
        <div class="thumb" onclick="openModal(this.closest(\'.card\'))">
          <img src="'.$thumb.'" alt="thumbnail" />
          <div class="play-btn"><div class="circle"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div></div>
          <span class="level">'. htmlspecialchars($course['level']) .'</span>
          <span class="duration">'. htmlspecialchars($course['duration']) .'</span>
        </div>
        <div class="card-body">
          <span class="cat">'. htmlspecialchars($course['cat']) .'</span>
          <h3 class="card-title">'. htmlspecialchars($course['title']) .'</h3>
          <p class="card-desc">'. htmlspecialchars($course['desc']) .'</p>
          <div class="instructor">
            <div class="avatar">'. strtoupper(substr($course['instructor'],0,2)) .'</div>
            <span class="instructor-name">'. htmlspecialchars($course['instructor']) .'</span>
          </div>
        </div>
      </article>';
    }
    ?>

  </div>
</section>
<div id="historyBox" style="display:none">
<h2>Your Watch History</h2>
<div class="history-grid" id="historyGrid">
<?php
$user_id = $_SESSION['user_id'];
// Join with courses table to get title, fallback to video_id for hardcoded ones
$hresult = $conn->query("SELECT wh.video_id, COALESCE(c.title, wh.video_id) as title
                          FROM watch_history wh
                          LEFT JOIN courses c ON c.video_id = wh.video_id
                          WHERE wh.user_id='$user_id'
                          ORDER BY wh.watched_at DESC");
if($hresult && $hresult->num_rows > 0){
  while($hrow = $hresult->fetch_assoc()){
    $video = $hrow['video_id'];
    $htitle = htmlspecialchars($hrow['title']);
    $thumb = "https://img.youtube.com/vi/$video/maxresdefault.jpg";
    echo "<div class='history-card' onclick=\"openHistoryVideo('$video', '$htitle')\">
<img src='$thumb' onerror=\"this.src='https://img.youtube.com/vi/$video/hqdefault.jpg'\">
<p>$htitle</p>
</div>";
  }
}else{
  echo "<p style='color:#aaa;padding:1rem'>No videos watched yet.</p>";
}
?>
</div>
</div>

<!-- Video Modal -->
<div class="modal-overlay" id="videoModal" onclick="handleOverlayClick(event)">
  <div class="modal-box" id="modalBox">
    <button class="modal-close" onclick="closeModal()" title="Close">✕</button>
    <div class="modal-video-wrap">
      <iframe id="modalIframe" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <div class="modal-info">
      <div class="modal-info-left">
        <div class="modal-cat" id="modalCat"></div>
        <h2 id="modalTitle"></h2>
        <p id="modalDesc"></p>
      </div>
      <button class="take-test-btn" id="takeTestBtn" onclick="openQuiz()">📝 Take Test</button>
    </div>
  </div>
</div>

<script>
function openModal(card) {
  const vid   = card.dataset.vid;
  const title = card.dataset.title;
  const cat   = card.dataset.cat;
  const desc  = card.dataset.desc;

  document.getElementById('modalIframe').src =
    `https://www.youtube.com/embed/${vid}?autoplay=1&rel=0&modestbranding=1&enablejsapi=1&origin=${window.location.origin}`;

  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalCat').textContent   = cat;
  document.getElementById('modalDesc').textContent  = desc;

  document.getElementById('videoModal').classList.add('open');
  document.body.style.overflow = 'hidden';

  // Save watched history
  fetch(`save_watch.php?video=${vid}`);
}

function closeModal() {
  document.getElementById('videoModal').classList.remove('open');
  document.getElementById('modalIframe').src = '';
  document.body.style.overflow = '';

  loadHistory(); // 🔥 auto update without refresh
}



function handleOverlayClick(e) {
  if (e.target === document.getElementById('videoModal')) closeModal();
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});
</script>
<script>

function showHistory() {
let box = document.getElementById("historyBox");

if (box.style.display === "block") {
    box.style.display = "none";
} else {
    box.style.display = "block";
    loadHistory(); // 🔥 load fresh data
}

}

</script>
<script>
 function openHistoryVideo(videoId, videoTitle){
  currentVid   = videoId;
  currentTitle = videoTitle || 'This Course';

  // Reset quiz state
  preGenDone  = false;
  currentQuiz = [];
  stopTimePolling();
  const btn = document.getElementById('takeTestBtn');
  btn.classList.remove('visible');
  btn.style.animation = '';

  document.getElementById('modalIframe').src =
    `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1&enablejsapi=1&origin=${window.location.origin}`;

  document.getElementById('modalTitle').textContent = videoTitle || 'Previously Watched Video';
  document.getElementById('modalCat').textContent   = 'History';
  document.getElementById('modalDesc').textContent  = 'Replay this video and take the quiz again.';

  document.getElementById('videoModal').classList.add('open');
  document.body.style.overflow = 'hidden';

  // Start time polling so Take Test button appears
  startTimePolling();

  fetch(`save_watch.php?video=${videoId}`);
}
function loadHistory(){
  const grid = document.getElementById('historyGrid');
  if(!grid) return;
  grid.innerHTML = "<p style='color:#aaa;padding:1rem'>Loading...</p>";
  fetch('fetch_history.php?nocache=' + Date.now())
    .then(r => r.text())
    .then(html => { grid.innerHTML = html || "<p style='color:#aaa;padding:1rem'>No videos watched yet.</p>"; })
    .catch(() => { grid.innerHTML = "<p style='color:#aaa;padding:1rem'>Could not load history.</p>"; });
}
</script>


<!-- ══ QUIZ OVERLAY ══ -->
<div id="quizOverlay">
  <div class="quiz-box">
    <div class="quiz-header">
      <h2 id="quizTitle">Course Quiz</h2>
      <div style="display:flex;align-items:center;gap:1rem;">
        <span class="quiz-progress" id="quizProgress">Question 1 of 10</span>
        <button onclick="closeQuiz()" title="Close Quiz" style="background:rgba(255,255,255,0.15);border:none;color:#fff;width:34px;height:34px;border-radius:50%;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s;flex-shrink:0;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
      </div>
    </div>
    <div class="progress-bar-wrap">
      <div class="progress-bar-fill" id="quizProgressBar" style="width:10%"></div>
    </div>
    <!-- AI Loading State -->
    <div id="quizLoadingMsg" style="display:none; text-align:center; padding:3rem 1rem;">
      <div style="font-size:3rem; margin-bottom:1rem;">🤖</div>
      <div style="color:#fff; font-size:1.1rem; font-weight:600; margin-bottom:.5rem;">Generating your quiz with AI...</div>
      <div style="color:#aaa; font-size:.9rem; margin-bottom:1.5rem;">Creating 10 questions based on: <span id="loadingCourseTitle" style="color:#667eea"></span></div>
      <div style="display:flex; justify-content:center; gap:.4rem;">
        <span style="width:10px;height:10px;border-radius:50%;background:#667eea;display:inline-block;animation:dot-bounce 1.2s infinite 0s"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#667eea;display:inline-block;animation:dot-bounce 1.2s infinite .2s"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#667eea;display:inline-block;animation:dot-bounce 1.2s infinite .4s"></span>
      </div>
    </div>
    <div id="quizQuestionArea">
      <div class="quiz-question" id="quizQ"></div>
      <div class="quiz-options" id="quizOpts"></div>
      <div class="quiz-feedback" id="quizFeedback"></div>
      <div class="quiz-nav">
        <button class="btn-next-q" id="btnNextQ" onclick="nextQuestion()">Next →</button>
      </div>
    </div>
    <div id="quizResultArea" style="display:none" class="quiz-result">
      <div class="result-emoji" id="resultEmoji"></div>
      <div class="result-score" id="resultScore"></div>
      <div class="result-msg"  id="resultMsg"></div>
      <div class="result-btns" id="resultBtns"></div>
    </div>
  </div>
</div>

<!-- ══ CERTIFICATE OVERLAY ══ -->
<div id="certOverlay">
  <button onclick="closeCert()" title="Close" id="certCloseBtn" style="position:fixed;top:18px;right:22px;background:rgba(255,255,255,0.12);border:2px solid rgba(255,255,255,0.25);color:#fff;width:44px;height:44px;border-radius:50%;font-size:1.25rem;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:10001;transition:all .2s;" onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">&#x2715;</button>
  <canvas id="certCanvas" width="960" height="660"></canvas>
  <div class="cert-actions">
    <button class="btn-download-cert" onclick="downloadCert()">&#8595; Download Certificate</button>
    <button class="btn-close-cert"    onclick="closeCert()">&#x2715; Close</button>
  </div>
</div>

<script>
/* ════════════════════════════════════════════════════
   QUIZ DATA — 10 questions per course (keyed by video_id)
   ════════════════════════════════════════════════════ */
// ════════════════════════════════════════
// AI-POWERED QUIZ GENERATOR
// Generates 10 questions based on video title via Claude API
// ════════════════════════════════════════
let currentQuiz  = [];
let aiQuizCache  = {}; // cache so we don't re-generate same video

async function generateQuizWithAI(videoTitle, videoId) {
  // Check cache first
  if(aiQuizCache[videoId]) return aiQuizCache[videoId];

  // Show loading state
  document.getElementById('quizLoadingMsg').style.display = 'block';
  document.getElementById('quizQuestionArea').style.display = 'none';

  try {
    const response = await fetch("https://api.anthropic.com/v1/messages", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        model: "claude-sonnet-4-20250514",
        max_tokens: 1000,
        messages: [{
          role: "user",
          content: `Generate exactly 10 multiple choice quiz questions specifically about the topic: "${videoTitle}".

Strict Rules:
- ALL 10 questions MUST be about "${videoTitle}" — nothing else
- Cover different aspects: concepts, syntax, usage, best practices, common errors
- 4 answer options per question
- Exactly 1 correct answer — vary its position (do NOT always put correct answer first)
- Mix easy, medium and hard questions
- Return ONLY a valid JSON array — no markdown, no explanation, no backticks

Required JSON format:
[
  {
    "q": "Question text here?",
    "opts": ["Option A", "Option B", "Option C", "Option D"],
    "ans": 2
  }
]

"ans" is the 0-based index (0,1,2 or 3) of the correct option.`
        }]
      })
    });

    const data = await response.json();
    const raw  = data.content[0].text;

    // Parse JSON — strip any markdown fences if present
    const clean = raw.replace(/```json|```/g, '').trim();
    const quiz  = JSON.parse(clean);

    // Cache it
    aiQuizCache[videoId] = quiz;
    return quiz;

  } catch(err) {
    console.error('AI quiz generation failed:', err);
    // Fallback: return generic questions
    return getFallbackQuiz(videoTitle);
  }
}

function getFallbackQuiz(title) {
  return [
    {q:`What is the main topic covered in "${title}"?`,opts:["Practical skills and concepts","Entertainment only","Unrelated theory","Language learning"],ans:0},
    {q:"Which approach best helps you learn this subject?",opts:["Practice with real projects","Reading notes only","Watching without doing","Memorising without understanding"],ans:0},
    {q:"What should you do when you get stuck on a problem?",opts:["Debug systematically and check documentation","Give up immediately","Copy paste blindly","Ignore the error"],ans:0},
    {q:"Why is version control important in development?",opts:["Track changes and collaborate safely","Makes code run faster","Styles the website","Connects to databases"],ans:0},
    {q:"What does 'responsive design' mean?",opts:["Layout works on all screen sizes","Fast animations","Colorful visuals","Dark mode only"],ans:0},
    {q:"Which is a best practice when naming variables?",opts:["Clear, descriptive names","x, y, z","Random letters","Numbers only"],ans:0},
    {q:"What is the purpose of writing comments in code?",opts:["Explain what the code does to others","Make code run faster","Add visual styling","Connect to APIs"],ans:0},
    {q:"What does API stand for?",opts:["Application Programming Interface","All Program Inputs","Advanced PHP Integration","Automated Process Index"],ans:0},
    {q:"What is debugging?",opts:["Finding and fixing errors in code","Writing new features","Designing the UI","Managing databases"],ans:0},
    {q:"What is the best way to improve your skills?",opts:["Build real projects consistently","Read textbooks only","Watch videos without practising","Memorise syntax"],ans:0}
  ];
}

function pauseVideo() {
  // Pause YouTube iframe by removing autoplay from src
  const iframe = document.getElementById('modalIframe');
  if(iframe && iframe.src) {
    // Post message to YouTube iframe to pause
    iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
  }
}

async function openQuiz() {
  currentQ  = 0;
  score     = 0;
  answered  = false;

  pauseVideo();

  document.getElementById('quizTitle').textContent          = '📝 ' + currentTitle + ' — Quiz';
  document.getElementById('quizQuestionArea').style.display  = 'none';
  document.getElementById('quizResultArea').style.display    = 'none';
  document.getElementById('quizLoadingMsg').style.display    = 'block';
  document.getElementById('loadingCourseTitle').textContent   = currentTitle;
  document.getElementById('quizOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';

  // ── Step 1: Try DB questions first ──
  const courseId = await getCourseIdByVideoId(currentVid);
  if(courseId){
    try {
      const res  = await fetch(`get_quiz.php?course_id=${courseId}`);
      const data = await res.json();
      if(data.success && data.questions.length > 0){
        // Convert DB format → quiz format
        currentQuiz = data.questions.map(q => {
          const opts = [q.option_a, q.option_b];
          if(q.option_c) opts.push(q.option_c);
          if(q.option_d) opts.push(q.option_d);
          const ansIdx = ['A','B','C','D'].indexOf(q.correct.toUpperCase());
          return { q: q.question, opts: opts, ans: ansIdx >= 0 ? ansIdx : 0 };
        });
        document.getElementById('quizLoadingMsg').style.display  = 'none';
        document.getElementById('quizQuestionArea').style.display = '';
        showQuestion();
        return;
      }
    } catch(e){}
  }

  // ── Step 2: Fallback to AI ──
  currentQuiz = await generateQuizWithAI(currentTitle, currentVid);
  document.getElementById('quizLoadingMsg').style.display  = 'none';
  document.getElementById('quizQuestionArea').style.display = '';
  showQuestion();
}

// Helper: get course_id from courses table via video_id
const _courseIdCache = {};
async function getCourseIdByVideoId(videoId){
  if(_courseIdCache[videoId] !== undefined) return _courseIdCache[videoId];
  try {
    const res  = await fetch(`get_course_id.php?video_id=${encodeURIComponent(videoId)}`);
    const data = await res.json();
    _courseIdCache[videoId] = data.course_id || null;
    return _courseIdCache[videoId];
  } catch(e){ return null; }
}

function shuffleArray(arr) {
  const a = [...arr];
  for(let i = a.length-1; i > 0; i--){
    const j = Math.floor(Math.random() * (i+1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

let currentShuffled = []; // shuffled options for current question
let currentCorrectIdx = 0; // index of correct answer after shuffle

function showQuestion() {
  const q   = currentQuiz[currentQ];
  const tot = currentQuiz.length;

  document.getElementById('quizProgress').textContent     = `Question ${currentQ+1} of ${tot}`;
  document.getElementById('quizProgressBar').style.width  = `${((currentQ+1)/tot)*100}%`;
  document.getElementById('quizQ').textContent            = q.q;
  document.getElementById('quizFeedback').style.display   = 'none';
  document.getElementById('btnNextQ').style.display       = 'none';
  answered = false;

  // Shuffle options — track where correct answer lands
  const correctText = q.opts[q.ans];
  const indices = q.opts.map((_,i) => i);
  const shuffledIndices = shuffleArray(indices);
  currentShuffled = shuffledIndices.map(i => q.opts[i]);
  currentCorrectIdx = currentShuffled.indexOf(correctText);

  const optsEl = document.getElementById('quizOpts');
  optsEl.innerHTML = '';
  currentShuffled.forEach((opt, i) => {
    const btn = document.createElement('button');
    btn.className   = 'quiz-option';
    btn.textContent = opt;
    btn.onclick     = () => selectAnswer(i, currentCorrectIdx);
    optsEl.appendChild(btn);
  });
}

function selectAnswer(selected, correct) {
  if(answered) return;
  answered = true;

  const opts = document.querySelectorAll('.quiz-option');
  opts.forEach(o => o.classList.add('disabled'));

  const fb = document.getElementById('quizFeedback');
  if(selected === correct){
    opts[selected].classList.add('correct');
    fb.className = 'quiz-feedback correct';
    fb.textContent = '✅ Correct! Well done.';
    score++;
  } else {
    opts[selected].classList.add('wrong');
    opts[correct].classList.add('correct');
    fb.className = 'quiz-feedback wrong';
    fb.textContent = `❌ Incorrect. The correct answer is: "${currentShuffled[correct]}"`;
  }
  fb.style.display = 'block';
  document.getElementById('btnNextQ').style.display = 'block';
}

function nextQuestion() {
  currentQ++;
  if(currentQ < currentQuiz.length){
    showQuestion();
  } else {
    showResult();
  }
}

function showResult() {
  document.getElementById('quizQuestionArea').style.display = 'none';
  document.getElementById('quizResultArea').style.display   = '';

  const total   = currentQuiz.length;
  const pct     = Math.round((score / total) * 100);
  const passed  = pct >= 60;

  document.getElementById('resultEmoji').textContent = passed ? '🎉' : '😔';
  document.getElementById('resultScore').textContent = `${score}/${total} (${pct}%)`;
  document.getElementById('resultScore').className   = 'result-score ' + (passed ? 'pass' : 'fail');

  let msg, btns;
  if(passed){
    msg  = `Congratulations! You scored ${pct}% and passed the quiz. Your certificate is ready to download!`;
    btns = `<button class="btn-cert" onclick="generateCertificate()">🏆 Get Certificate</button>
            <button class="btn-retry" onclick="retryQuiz()">🔄 Retry Quiz</button>`;
  } else {
    msg  = `You scored ${pct}% — you need at least 60% to pass and earn a certificate. Please watch the video again carefully, then retry the test.`;
    btns = `<button class="btn-rewatch" onclick="rewatchVideo()">▶ Watch the Video Again</button>
            <button class="btn-retry" onclick="retryQuiz()">🔄 Retry Quiz</button>`;
  }
  document.getElementById('resultMsg').textContent  = msg;
  document.getElementById('resultBtns').innerHTML   = btns;
}

function retryQuiz() {
  currentQ = 0; score = 0; answered = false;
  document.getElementById('quizQuestionArea').style.display = '';
  document.getElementById('quizResultArea').style.display   = 'none';
  showQuestion();
}

function closeQuiz() {
  document.getElementById('quizOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function rewatchVideo() {
  document.getElementById('quizOverlay').classList.remove('open');
  document.body.style.overflow = '';
  // Re-open the video modal
  document.getElementById('videoModal').classList.add('open');
  document.getElementById('modalIframe').src =
    `https://www.youtube.com/embed/${currentVid}?autoplay=1&rel=0&modestbranding=1&enablejsapi=1&origin=${window.location.origin}`;
  document.body.style.overflow = 'hidden';
}

/* ════════════════════════════════════════
   CERTIFICATE GENERATOR (Canvas)
   ════════════════════════════════════════ */
function generateCertificate() {
  const userName = '<?php echo isset($_SESSION["name"]) ? addslashes($_SESSION["name"]) : "Student"; ?>';
  const canvas   = document.getElementById('certCanvas');
  const ctx      = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;

  ctx.clearRect(0, 0, W, H);

  // ── OUTER NAVY BACKGROUND ──
  ctx.fillStyle = '#1B2A4A';
  ctx.fillRect(0, 0, W, H);

  // ── WHITE CENTER AREA ──
  ctx.fillStyle = '#FAFBFC';
  ctx.fillRect(52, 52, W-104, H-104);

  // ── OUTER NAVY BORDER (gold trim lines) ──
  // outer gold line
  ctx.strokeStyle = '#C9A84C';
  ctx.lineWidth = 2;
  ctx.strokeRect(8, 8, W-16, H-16);
  // inner gold line inside navy
  ctx.strokeStyle = 'rgba(201,168,76,0.5)';
  ctx.lineWidth = 1;
  ctx.strokeRect(18, 18, W-36, H-36);
  // gold line bordering white area
  ctx.strokeStyle = '#C9A84C';
  ctx.lineWidth = 1.5;
  ctx.strokeRect(52, 52, W-104, H-104);
  // inner thin line on white
  ctx.strokeStyle = 'rgba(201,168,76,0.45)';
  ctx.lineWidth = 0.7;
  ctx.strokeRect(60, 60, W-120, H-120);

  // ── CORNER ORNAMENTS (filigree style) ──
  function filigree(ox, oy, sx, sy) {
    ctx.save();
    ctx.translate(ox, oy);
    ctx.scale(sx, sy);
    ctx.strokeStyle = '#C9A84C';
    ctx.lineWidth = 1.2;
    // outer L
    ctx.beginPath(); ctx.moveTo(0,36); ctx.lineTo(0,0); ctx.lineTo(36,0); ctx.stroke();
    // inner L
    ctx.lineWidth = 0.7;
    ctx.beginPath(); ctx.moveTo(0,24); ctx.lineTo(0,8); ctx.lineTo(24,8); ctx.stroke();
    // diamond dot
    ctx.fillStyle = '#C9A84C';
    ctx.beginPath();
    ctx.moveTo(0,-4); ctx.lineTo(4,0); ctx.lineTo(0,4); ctx.lineTo(-4,0);
    ctx.closePath(); ctx.fill();
    // small circle
    ctx.beginPath(); ctx.arc(8, 8, 2.5, 0, Math.PI*2); ctx.fill();
    ctx.restore();
  }
  filigree(52, 52,  1,  1);
  filigree(W-52, 52, -1,  1);
  filigree(52, H-52, 1, -1);
  filigree(W-52, H-52, -1, -1);

  // ── BOTTOM NAVY BAR ──
  ctx.fillStyle = '#1B2A4A';
  ctx.fillRect(52, H-120, W-104, 68);
  // top gold trim on bottom bar
  ctx.strokeStyle = '#C9A84C';
  ctx.lineWidth = 1.2;
  ctx.beginPath(); ctx.moveTo(52, H-120); ctx.lineTo(W-52, H-120); ctx.stroke();
  ctx.strokeStyle = 'rgba(201,168,76,0.4)';
  ctx.lineWidth = 0.6;
  ctx.beginPath(); ctx.moveTo(52, H-114); ctx.lineTo(W-52, H-114); ctx.stroke();

  // ── TOP RIGHT: LEARNHUB text ──
  ctx.font = 'bold 16px Georgia, serif';
  ctx.fillStyle = '#1B2A4A';
  ctx.textAlign = 'center';
  ctx.fillText('LEARNHUB', W*0.67, 102);
  ctx.font = '10px Georgia, serif';
  ctx.fillStyle = '#5a6a7a';
  ctx.fillText('ONLINE LEARNING PLATFORM', W*0.67, 118);

  // ── LEFT SIDE: GRADUATION BADGE ──
  // Gold ribbon vertical strip behind badge
  const ribX = 148, ribTop = 52, ribW = 52;
  // ribbon body gold
  ctx.fillStyle = '#C9A84C';
  ctx.fillRect(ribX, ribTop, ribW, H-120-52+30);
  // ribbon vertical lines (decorative)
  ctx.strokeStyle = 'rgba(255,255,255,0.18)';
  ctx.lineWidth = 1;
  for(let i=1; i<6; i++){
    ctx.beginPath();
    ctx.moveTo(ribX + i*(ribW/6), ribTop+4);
    ctx.lineTo(ribX + i*(ribW/6), ribTop + H-180);
    ctx.stroke();
  }
  // ribbon bottom chevron point
  const rbY = H-120-52+30+ribTop;
  ctx.fillStyle = '#C9A84C';
  ctx.beginPath();
  ctx.moveTo(ribX, rbY);
  ctx.lineTo(ribX+ribW/2, rbY+36);
  ctx.lineTo(ribX+ribW, rbY);
  ctx.closePath(); ctx.fill();
  // dark triangle at ribbon bottom
  ctx.fillStyle = '#1B2A4A';
  ctx.beginPath();
  ctx.moveTo(ribX, rbY+10);
  ctx.lineTo(ribX+ribW/2, rbY+36);
  ctx.lineTo(ribX+ribW, rbY+10);
  ctx.closePath(); ctx.fill();

  // Badge circle (navy)
  const bx = ribX + ribW/2, by = 170, br = 70;
  // outer gold ring
  ctx.fillStyle = '#C9A84C';
  ctx.beginPath(); ctx.arc(bx, by, br, 0, Math.PI*2); ctx.fill();
  // navy inner
  ctx.fillStyle = '#1B2A4A';
  ctx.beginPath(); ctx.arc(bx, by, br-8, 0, Math.PI*2); ctx.fill();
  // inner gold ring
  ctx.strokeStyle = '#C9A84C';
  ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.arc(bx, by, br-14, 0, Math.PI*2); ctx.stroke();

  // Graduation cap (mortar board)
  ctx.fillStyle = '#C9A84C';
  // board top
  const capY = by - 22;
  ctx.beginPath();
  ctx.moveTo(bx, capY-18);
  ctx.lineTo(bx+36, capY);
  ctx.lineTo(bx, capY+18);
  ctx.lineTo(bx-36, capY);
  ctx.closePath(); ctx.fill();
  // cap top square
  ctx.fillRect(bx-20, capY-30, 40, 20);
  ctx.fillRect(bx-22, capY-32, 44, 8);
  // tassel (right side)
  ctx.strokeStyle = '#C9A84C';
  ctx.lineWidth = 2;
  ctx.beginPath(); ctx.moveTo(bx+18, capY-26); ctx.lineTo(bx+30, capY-10); ctx.stroke();
  ctx.beginPath(); ctx.arc(bx+30, capY-6, 5, 0, Math.PI*2); ctx.fill();
  // tassel strings
  ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(bx+30, capY-2); ctx.lineTo(bx+26, capY+14); ctx.stroke();
  ctx.beginPath(); ctx.moveTo(bx+30, capY-2); ctx.lineTo(bx+30, capY+16); ctx.stroke();
  ctx.beginPath(); ctx.moveTo(bx+30, capY-2); ctx.lineTo(bx+34, capY+14); ctx.stroke();

  // bottom arc of cap
  ctx.strokeStyle = '#C9A84C'; ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.arc(bx, by-2, 24, 0.2, Math.PI-0.2); ctx.stroke();

  // ── RIGHT SIDE CONTENT ──
  const cx = (W*0.35 + W-52)/2 + 20;

  // CERTIFICATE big text
  ctx.font = 'bold 58px Georgia, serif';
  ctx.fillStyle = '#1B2A4A';
  ctx.textAlign = 'center';
  ctx.fillText('CERTIFICATE', cx, 195);

  // OF COMPLETION
  ctx.font = 'bold 15px Georgia, serif';
  ctx.fillStyle = '#1B2A4A';
  ctx.letterSpacing = '4px';
  ctx.fillText('OF COMPLETION', cx, 222);
  ctx.letterSpacing = '0px';

  // thin gold rule
  ctx.strokeStyle = '#C9A84C'; ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(cx-200, 234); ctx.lineTo(cx+200, 234); ctx.stroke();

  // "This certificate is proudly awarded to"
  ctx.font = 'italic 14px Georgia, serif';
  ctx.fillStyle = '#555';
  ctx.fillText('This certificate is proudly awarded to', cx, 262);

  // Student name
  ctx.font = 'italic bold 46px Georgia, serif';
  ctx.fillStyle = '#1B2A4A';
  ctx.fillText(userName, cx, 316);

  // name underline
  const nw2 = ctx.measureText(userName).width;
  ctx.strokeStyle = '#1B2A4A'; ctx.lineWidth = 1.2;
  ctx.beginPath();
  ctx.moveTo(cx - nw2/2 - 10, 326);
  ctx.lineTo(cx + nw2/2 + 10, 326);
  ctx.stroke();

  // "for successfully completing the course"
  ctx.font = 'italic 14px Georgia, serif';
  ctx.fillStyle = '#555';
  ctx.fillText('for successfully completing the course', cx, 355);

  // Course title
  ctx.font = 'bold 24px Georgia, serif';
  ctx.fillStyle = '#1B2A4A';
  const wrds = currentTitle.split(' ');
  let ln='', lns=[], mxw=380;
  for(let w of wrds){
    const t = ln ? ln+' '+w : w;
    ctx.font = 'bold 24px Georgia, serif';
    if(ctx.measureText(t).width > mxw && ln){ lns.push(ln); ln=w; } else ln=t;
  }
  lns.push(ln);
  lns.forEach((l,i) => ctx.fillText(l, cx, 386 + i*32));

  const aY = 386 + lns.length*32;

  // Score pill
  const pct = Math.round((score / currentQuiz.length) * 100);
  const pillTxt = `Score: ${score}/${currentQuiz.length} (${pct}%)`;
  ctx.font = 'bold 13px Georgia, serif';
  const pillW2 = ctx.measureText(pillTxt).width + 40;
  const pillX2 = cx - pillW2/2, pillY2 = aY+14;
  ctx.strokeStyle = '#1B2A4A'; ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.roundRect(pillX2, pillY2, pillW2, 30, 15); ctx.stroke();
  ctx.fillStyle = '#1B2A4A';
  ctx.fillText(pillTxt, cx, pillY2+20);

  // ── BOTTOM BAR CONTENT ──
  // Left label
  ctx.font = 'bold 13px Georgia, serif';
  ctx.fillStyle = '#C9A84C';
  ctx.textAlign = 'center';
  ctx.fillText('LEARNHUB', W*0.32, H-88);
  ctx.font = '11px Georgia, serif';
  ctx.fillStyle = 'rgba(201,168,76,0.8)';
  ctx.fillText('Platform Director', W*0.32, H-70);

  // Right label
  ctx.font = 'bold 13px Georgia, serif';
  ctx.fillStyle = '#C9A84C';
  ctx.fillText('CERTIFIED', W*0.68, H-88);
  ctx.font = '11px Georgia, serif';
  ctx.fillStyle = 'rgba(201,168,76,0.8)';
  ctx.fillText('Verified Achievement', W*0.68, H-70);

  // Center seal in bottom bar
  const sx = W/2, sy = H-86, sr2 = 28;
  ctx.fillStyle = '#2a3a5a';
  ctx.beginPath(); ctx.arc(sx, sy, sr2, 0, Math.PI*2); ctx.fill();
  ctx.strokeStyle = '#C9A84C'; ctx.lineWidth = 2;
  ctx.beginPath(); ctx.arc(sx, sy, sr2, 0, Math.PI*2); ctx.stroke();
  ctx.strokeStyle = 'rgba(201,168,76,0.5)'; ctx.lineWidth = 0.8;
  ctx.setLineDash([3,2]);
  ctx.beginPath(); ctx.arc(sx, sy, sr2-6, 0, Math.PI*2); ctx.stroke();
  ctx.setLineDash([]);
  // star
  function star5(cx2,cy2,or,ir){
    ctx.beginPath();
    for(let i=0;i<10;i++){
      const a=(i*Math.PI/5)-Math.PI/2;
      const r2=i%2===0?or:ir;
      i===0?ctx.moveTo(cx2+Math.cos(a)*r2,cy2+Math.sin(a)*r2):ctx.lineTo(cx2+Math.cos(a)*r2,cy2+Math.sin(a)*r2);
    }
    ctx.closePath();
    ctx.fillStyle='#C9A84C'; ctx.fill();
  }
  star5(sx, sy, 13, 5);

  // ── DATE ──
  const today = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
  ctx.font = '10px Georgia, serif';
  ctx.fillStyle = 'rgba(201,168,76,0.5)';
  ctx.textAlign = 'center';
  ctx.fillText(today, W/2, H-16);

  // ── TOP CENTER DIAMOND in navy ──
  ctx.fillStyle = '#C9A84C';
  ctx.beginPath();
  ctx.moveTo(W/2, 40); ctx.lineTo(W/2+8, 52); ctx.lineTo(W/2, 64); ctx.lineTo(W/2-8, 52);
  ctx.closePath(); ctx.fill();

  // bottom center diamond
  ctx.beginPath();
  ctx.moveTo(W/2, H-64); ctx.lineTo(W/2+8, H-52); ctx.lineTo(W/2, H-40); ctx.lineTo(W/2-8, H-52);
  ctx.closePath(); ctx.fill();

  document.getElementById('certOverlay').classList.add('open');
}

function downloadCert() {
  const canvas = document.getElementById('certCanvas');
  const a = document.createElement('a');
  a.download = `LearnHub_Certificate_${currentTitle.replace(/\s+/g,'_')}.png`;
  a.href = canvas.toDataURL('image/png');
  a.click();
}

function closeCert() {
  document.getElementById('certOverlay').classList.remove('open');
}

/* ════════════════════════════════════════
   PATCH openModal to store current video
   and show Take Test button after 5 sec
   ════════════════════════════════════════ */
// ════════════════════════════════════════
// YOUTUBE VIDEO END DETECTION + 10s PRE-FETCH
// ════════════════════════════════════════
let preGenDone    = false; // quiz already pre-generated flag
let pollInterval  = null;  // interval to poll video time

// Listen to YouTube iframe messages
window.addEventListener('message', function(e) {
  if(!e.data) return;
  try {
    const data = typeof e.data === 'string' ? JSON.parse(e.data) : e.data;

    // State 1 = playing → start polling current time
    if(data.event === 'onStateChange' && data.info === 1) {
      startTimePolling();
    }
    // State 2 = paused → stop polling
    if(data.event === 'onStateChange' && data.info === 2) {
      stopTimePolling();
    }
    // State 0 = ended → show button
    if(data.event === 'onStateChange' && data.info === 0) {
      stopTimePolling();
      onVideoEnded();
    }
    // Receive current time + duration from iframe
    if(data.event === 'infoDelivery' && data.info) {
      const cur  = data.info.currentTime;
      const dur  = data.info.duration;
      if(dur > 0 && !preGenDone && (dur - cur) <= 600) {
        preGenDone = true;
        // 10 minutes before end: pre-generate quiz AND show button
        generateQuizWithAI(currentTitle, currentVid);
        showTestButton();
      }
    }
  } catch(err) {}
});

function startTimePolling() {
  stopTimePolling();
  const iframe = document.getElementById('modalIframe');
  // Poll every 2 seconds — ask YouTube iframe for current time
  pollInterval = setInterval(() => {
    if(iframe && iframe.contentWindow) {
      iframe.contentWindow.postMessage(
        JSON.stringify({event:'listening', id:'ytframe'}), '*'
      );
      iframe.contentWindow.postMessage(
        JSON.stringify({event:'command', func:'getCurrentTime', args:''}), '*'
      );
    }
  }, 2000);
}

function stopTimePolling() {
  if(pollInterval) { clearInterval(pollInterval); pollInterval = null; }
}

function onVideoEnded() {
  // Show button when video ends (if not already shown)
  showTestButton();
}

function showTestButton() {
  const btn = document.getElementById('takeTestBtn');
  if(!btn.classList.contains('visible')) {
    btn.classList.add('visible');
    btn.style.animation = 'pulse 1s ease-in-out 3';
  }
}

const _origOpenModal = openModal;

openModal = function(card) {
  _origOpenModal(card);
  currentVid   = card.dataset.vid;
  currentTitle = card.dataset.title || 'This Course';

  // Reset state for new video
  preGenDone = false;
  stopTimePolling();
  currentQuiz = [];

  // Hide test button
  const btn = document.getElementById('takeTestBtn');
  btn.classList.remove('visible');
  btn.style.animation = '';

  // Ask YouTube to send state events
  setTimeout(() => {
    const iframe = document.getElementById('modalIframe');
    if(iframe && iframe.contentWindow) {
      iframe.contentWindow.postMessage(
        JSON.stringify({event:'listening', id:'ytframe'}), '*'
      );
    }
  }, 1500);
};

const _origCloseModal = closeModal;
closeModal = function() {
  _origCloseModal();
  stopTimePolling();
  preGenDone = false;
  document.getElementById('takeTestBtn').classList.remove('visible');
};
</script>

</body>
</html>