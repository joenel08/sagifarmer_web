<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SAGIPfarmer — Smart Water & Soil Monitoring for Rice Fields</title>
<meta name="description" content="SAGIPfarmer is an IoT-based, solar-powered mobile application for real-time water level and soil moisture monitoring in rice fields.">
<link rel="icon" type="image/x-icon" href="images/logo_sagip.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ============ RESET & TOKENS ============ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --green-50:#f0fdf4;
  --green-100:#dcfce7;
  --green-200:#bbf7d0;
  --green-300:#86efac;
  --green-400:#4ade80;
  --green-500:#22c55e;
  --green-600:#16a34a;
  --green-700:#15803d;
  --green-800:#166534;
  --green-900:#14532d;
  --green-950:#052e16;
  --lime:#a3e635;
  --ink:#0b1a12;
  --muted:#5b6f63;
  --line:#e2efe6;
  --white:#ffffff;
  --radius:18px;
  --radius-lg:28px;
  --shadow-sm:0 2px 8px rgba(6,78,59,.06);
  --shadow:0 12px 30px -10px rgba(6,78,59,.18);
  --shadow-lg:0 30px 60px -20px rgba(6,78,59,.28);
  --max:1180px;
}

html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{
  font-family:'Plus Jakarta Sans',system-ui,-apple-system,"Segoe UI",sans-serif;
  color:var(--ink);
  background:var(--white);
  line-height:1.65;
  overflow-x:hidden;
}
img,svg{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
button{font:inherit;cursor:pointer;border:none;background:none}

.wrap{width:100%;max-width:var(--max);margin-inline:auto;padding-inline:24px}

/* ============ BUTTONS ============ */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:10px;
  padding:15px 28px;border-radius:999px;font-weight:700;font-size:1rem;
  letter-spacing:-.01em;transition:.25s cubic-bezier(.4,0,.2,1);
  white-space:nowrap;position:relative;
}
.btn svg{flex-shrink:0}

.btn-primary{
  background:linear-gradient(135deg,var(--green-500),var(--green-700));
  color:#fff;
  box-shadow:0 10px 24px -8px rgba(22,163,74,.65);
}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 18px 34px -10px rgba(22,163,74,.7)}
.btn-primary:active{transform:translateY(-1px)}

.btn-ghost{
  background:rgba(255,255,255,.75);
  color:var(--green-800);
  border:1.5px solid var(--green-200);
  backdrop-filter:blur(8px);
}
.btn-ghost:hover{background:#fff;border-color:var(--green-400);transform:translateY(-3px)}

.btn-outline{
  background:transparent;color:var(--green-700);
  border:1.5px solid var(--green-300);
}
.btn-outline:hover{background:var(--green-50);border-color:var(--green-500)}

/* ============ HEADER ============ */
header{
  position:sticky;top:0;z-index:100;
  background:rgba(255,255,255,.82);
  backdrop-filter:blur(16px) saturate(180%);
  border-bottom:1px solid transparent;
  transition:.3s;
}
header.scrolled{border-bottom-color:var(--line);box-shadow:0 4px 20px -8px rgba(6,78,59,.12)}

.nav{display:flex;align-items:center;justify-content:space-between;height:76px;gap:20px}

.logo{display:flex;align-items:center;gap:11px;font-weight:800;font-size:1.2rem;letter-spacing:-.03em}

/* ---------- Logo mark (image + SVG fallback) ---------- */
.logo-mark{
  width:42px;height:42px;border-radius:13px;flex-shrink:0;
  background:linear-gradient(140deg,var(--green-400),var(--green-700));
  display:grid;place-items:center;
  box-shadow:0 6px 16px -4px rgba(22,163,74,.5);
  overflow:hidden;
}
.logo-mark img{width:100%;height:100%;object-fit:cover;display:block}
.logo-mark svg{display:none;width:22px;height:22px}

.logo-text small{
  display:block;font-size:.62rem;font-weight:600;letter-spacing:.14em;
  text-transform:uppercase;color:var(--green-600);line-height:1.2;margin-top:-2px;
}

.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{
  padding:9px 15px;border-radius:10px;font-size:.92rem;font-weight:600;
  color:var(--muted);transition:.2s;
}
.nav-links a:hover{color:var(--green-700);background:var(--green-50)}

.nav-cta{display:flex;align-items:center;gap:10px}
.nav-cta .btn{padding:11px 22px;font-size:.9rem}

.burger{display:none;width:44px;height:44px;border-radius:12px;place-items:center;background:var(--green-50)}
.burger span{display:block;width:19px;height:2px;background:var(--green-800);border-radius:2px;position:relative;transition:.3s}
.burger span::before,.burger span::after{
  content:'';position:absolute;left:0;width:19px;height:2px;background:var(--green-800);border-radius:2px;transition:.3s;
}
.burger span::before{top:-6px}
.burger span::after{top:6px}
.burger.open span{background:transparent}
.burger.open span::before{transform:translateY(6px) rotate(45deg)}
.burger.open span::after{transform:translateY(-6px) rotate(-45deg)}

/* ============ HERO ============ */
.hero{
  position:relative;overflow:hidden;
  padding:76px 0 96px;
  background:
    radial-gradient(900px 500px at 88% -5%, var(--green-100) 0%, transparent 62%),
    radial-gradient(700px 460px at 2% 8%, #e8fbef 0%, transparent 60%),
    linear-gradient(180deg,var(--green-50) 0%, #ffffff 82%);
}
.hero::before{
  content:'';position:absolute;inset:0;pointer-events:none;opacity:.5;
  background-image:linear-gradient(var(--green-100) 1px,transparent 1px),linear-gradient(90deg,var(--green-100) 1px,transparent 1px);
  background-size:58px 58px;
  mask-image:radial-gradient(circle at 50% 20%,#000 0%,transparent 72%);
  -webkit-mask-image:radial-gradient(circle at 50% 20%,#000 0%,transparent 72%);
}
.hero .wrap{position:relative;z-index:2}
.hero-grid{
  display:grid;grid-template-columns:1.08fr .92fr;gap:64px;align-items:center;
}

.pill{
  display:inline-flex;align-items:center;gap:9px;
  padding:7px 16px 7px 8px;border-radius:999px;
  background:#fff;border:1px solid var(--green-200);
  font-size:.8rem;font-weight:700;color:var(--green-800);
  box-shadow:var(--shadow-sm);margin-bottom:24px;
}
.pill b{
  background:linear-gradient(135deg,var(--green-500),var(--green-700));
  color:#fff;font-size:.66rem;font-weight:800;letter-spacing:.09em;
  padding:4px 10px;border-radius:999px;text-transform:uppercase;
}

.hero h1{
  font-size:clamp(2.35rem,5.1vw,3.9rem);
  line-height:1.06;letter-spacing:-.04em;font-weight:800;
  color:var(--green-950);margin-bottom:22px;
}
.hero h1 .hl{
  background:linear-gradient(120deg,var(--green-600),var(--green-400) 55%,var(--lime));
  -webkit-background-clip:text;background-clip:text;color:transparent;
}
.hero p.lede{
  font-size:1.11rem;color:var(--muted);max-width:560px;margin-bottom:34px;
}
.hero p.lede strong{color:var(--green-800);font-weight:700}

.hero-actions{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:30px}

.hero-meta{display:flex;flex-wrap:wrap;gap:24px;font-size:.85rem;color:var(--muted);font-weight:600}
.hero-meta div{display:flex;align-items:center;gap:8px}
.hero-meta svg{color:var(--green-600);flex-shrink:0}

/* ---- Phone stage ---- */
.phone-stage{position:relative;display:grid;place-items:center;padding:16px}
.phone-stage::before{
  content:'';position:absolute;width:340px;height:340px;border-radius:50%;
  background:radial-gradient(circle,rgba(74,222,128,.4),transparent 68%);
  filter:blur(30px);z-index:0;
}

/* --- Image phone (primary) --- */
.phone-shot{
  position:relative;z-index:1;
  width:290px;
  grid-area:1/1;
  animation:float 6s ease-in-out infinite;
  filter:drop-shadow(0 36px 50px rgba(6,78,59,.35));
}
.phone-shot img{width:100%;height:auto;display:block}

/* --- CSS phone (fallback, shown only if image fails) --- */
#phoneFallback{display:none;grid-area:1/1;position:relative;z-index:1}
.phone{
  position:relative;
  width:290px;height:590px;border-radius:44px;
  background:linear-gradient(160deg,#1d3a2a,#052e16);
  padding:11px;
  box-shadow:var(--shadow-lg),0 0 0 1.5px rgba(255,255,255,.4) inset;
  animation:float 6s ease-in-out infinite;
}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}

.screen{
  width:100%;height:100%;border-radius:34px;overflow:hidden;
  background:linear-gradient(180deg,#f6fdf8,#eafaf0);
  display:flex;flex-direction:column;position:relative;
}
.notch{
  position:absolute;top:11px;left:50%;transform:translateX(-50%);
  width:82px;height:20px;border-radius:999px;background:#0b1a12;z-index:5;
}
.app-bar{
  padding:32px 18px 14px;
  background:linear-gradient(135deg,var(--green-600),var(--green-800));
  color:#fff;display:flex;align-items:center;justify-content:space-between;
}
.app-bar .t{font-size:.72rem;opacity:.82;font-weight:600;letter-spacing:.02em}
.app-bar .n{font-size:.98rem;font-weight:800;letter-spacing:-.02em}
.live{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.28);
  padding:5px 11px;border-radius:999px;font-size:.62rem;font-weight:800;letter-spacing:.08em;
}
.live i{width:6px;height:6px;border-radius:50%;background:#a3e635;animation:blink 1.4s infinite}
@keyframes blink{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(163,230,53,.7)}50%{opacity:.45;box-shadow:0 0 0 5px rgba(163,230,53,0)}}

.app-body{padding:16px;display:flex;flex-direction:column;gap:13px;flex:1;overflow:hidden}

.gauge-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.gauge-card{
  background:#fff;border-radius:18px;padding:14px 10px;text-align:center;
  box-shadow:0 4px 14px -6px rgba(6,78,59,.16);
  border:1px solid var(--green-100);
}
.gauge{
  width:78px;height:78px;border-radius:50%;margin:0 auto 9px;
  display:grid;place-items:center;position:relative;
  background:conic-gradient(var(--gc,var(--green-500)) calc(var(--p,0)*1%), var(--green-100) 0);
  transition:background .8s ease;
}
.gauge::after{
  content:'';position:absolute;inset:8px;border-radius:50%;background:#fff;
  box-shadow:inset 0 1px 4px rgba(6,78,59,.08);
}
.gauge span{position:relative;z-index:2;font-size:1.02rem;font-weight:800;color:var(--green-900);letter-spacing:-.03em}
.gauge span em{font-style:normal;font-size:.58rem;font-weight:700;color:var(--muted);display:block;line-height:1;margin-top:1px}
.gauge-label{font-size:.66rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.06em}

.stat-list{display:flex;flex-direction:column;gap:9px}
.stat-row{
  background:#fff;border-radius:14px;padding:11px 14px;
  display:flex;align-items:center;gap:11px;
  box-shadow:0 3px 10px -5px rgba(6,78,59,.14);
  border:1px solid var(--green-100);
}
.stat-row .ic{
  width:31px;height:31px;border-radius:9px;display:grid;place-items:center;flex-shrink:0;
  background:var(--green-50);color:var(--green-700);
}
.stat-row .txt{flex:1;min-width:0}
.stat-row .txt b{display:block;font-size:.76rem;font-weight:800;color:var(--green-950);letter-spacing:-.01em}
.stat-row .txt small{font-size:.63rem;color:var(--muted);font-weight:600}
.stat-row .val{font-size:.82rem;font-weight:800;color:var(--green-700);white-space:nowrap}

.alert-chip{
  margin-top:auto;background:linear-gradient(120deg,var(--green-700),var(--green-600));
  color:#fff;border-radius:14px;padding:11px 14px;font-size:.68rem;font-weight:600;
  display:flex;align-items:center;gap:9px;line-height:1.4;
}
.alert-chip svg{flex-shrink:0}

/* floating badges */
.float-badge{
  position:absolute;z-index:3;background:#fff;border-radius:15px;
  padding:11px 16px;box-shadow:var(--shadow);border:1px solid var(--green-100);
  display:flex;align-items:center;gap:10px;
  animation:float 7s ease-in-out infinite;
}
.float-badge.a{top:11%;left:-14px;animation-delay:-1.5s}
.float-badge.b{bottom:14%;right:-20px;animation-delay:-3.5s}
.float-badge .ic{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:var(--green-50);color:var(--green-700)}
.float-badge b{display:block;font-size:.78rem;font-weight:800;color:var(--green-950);letter-spacing:-.02em;line-height:1.25}
.float-badge small{font-size:.62rem;color:var(--muted);font-weight:600}

/* ============ LOGO / STATS STRIP ============ */
.strip{
  border-block:1px solid var(--line);
  background:var(--green-50);
  padding:34px 0;
}
.strip-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center;
}
.strip-grid .num{
  font-size:clamp(1.5rem,3vw,2.1rem);font-weight:800;letter-spacing:-.045em;
  color:var(--green-700);line-height:1;
}
.strip-grid .lbl{font-size:.78rem;color:var(--muted);font-weight:700;margin-top:7px;letter-spacing:.01em}

/* ============ SECTION SHELL ============ */
section{padding:92px 0}
.sec-head{max-width:660px;margin:0 auto 56px;text-align:center}
.eyebrow{
  display:inline-block;font-size:.72rem;font-weight:800;letter-spacing:.15em;
  text-transform:uppercase;color:var(--green-600);margin-bottom:14px;
}
.sec-head h2{
  font-size:clamp(1.85rem,3.9vw,2.7rem);line-height:1.13;
  letter-spacing:-.038em;font-weight:800;color:var(--green-950);margin-bottom:16px;
}
.sec-head p{color:var(--muted);font-size:1.02rem}

/* ============ FEATURES ============ */
.features{background:#fff}
.f-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.f-card{
  background:#fff;border:1px solid var(--line);border-radius:var(--radius-lg);
  padding:32px 28px;transition:.3s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden;
}
.f-card::after{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--green-500),var(--lime));
  transform:scaleX(0);transform-origin:left;transition:transform .35s;
}
.f-card:hover{transform:translateY(-6px);box-shadow:var(--shadow);border-color:var(--green-200)}
.f-card:hover::after{transform:scaleX(1)}
.f-ic{
  width:54px;height:54px;border-radius:16px;display:grid;place-items:center;
  background:linear-gradient(140deg,var(--green-50),var(--green-100));
  color:var(--green-700);margin-bottom:20px;border:1px solid var(--green-100);
}
.f-card h3{font-size:1.09rem;font-weight:800;letter-spacing:-.025em;color:var(--green-950);margin-bottom:9px}
.f-card p{font-size:.9rem;color:var(--muted);line-height:1.66}

/* ============ HOW IT WORKS ============ */
.how{background:linear-gradient(180deg,#fff,var(--green-50))}
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;position:relative}
.steps::before{
  content:'';position:absolute;top:32px;left:11%;right:11%;height:2px;
  background:repeating-linear-gradient(90deg,var(--green-300) 0 8px,transparent 8px 16px);
  z-index:0;
}
.step{position:relative;z-index:1;text-align:center;padding:0 6px}
.step .dot{
  width:64px;height:64px;margin:0 auto 20px;border-radius:20px;
  background:#fff;border:2px solid var(--green-200);
  display:grid;place-items:center;color:var(--green-700);
  box-shadow:0 8px 20px -10px rgba(6,78,59,.3);
  font-weight:800;
}
.step .n{
  position:absolute;top:-6px;left:50%;transform:translateX(18px);
  width:24px;height:24px;border-radius:50%;
  background:linear-gradient(135deg,var(--green-500),var(--green-700));color:#fff;
  font-size:.7rem;font-weight:800;display:grid;place-items:center;
  box-shadow:0 4px 10px -3px rgba(22,163,74,.7);
}
.step h4{font-size:1rem;font-weight:800;letter-spacing:-.02em;color:var(--green-950);margin-bottom:8px}
.step p{font-size:.85rem;color:var(--muted);line-height:1.62}

/* ============ HARDWARE ============ */
.hardware{background:#fff}
.hw-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
.hw-list{display:flex;flex-direction:column;gap:14px;margin-top:30px}
.hw-item{
  display:flex;gap:16px;align-items:flex-start;
  background:var(--green-50);border:1px solid var(--green-100);
  border-radius:var(--radius);padding:18px 20px;transition:.25s;
}
.hw-item:hover{background:#fff;box-shadow:var(--shadow-sm);transform:translateX(4px)}
.hw-item .ic{
  width:42px;height:42px;border-radius:12px;flex-shrink:0;display:grid;place-items:center;
  background:linear-gradient(140deg,var(--green-500),var(--green-700));color:#fff;
}
.hw-item b{display:block;font-size:.95rem;font-weight:800;color:var(--green-950);letter-spacing:-.02em;margin-bottom:3px}
.hw-item p{font-size:.85rem;color:var(--muted);line-height:1.6}

.hw-visual{
  border-radius:var(--radius-lg);padding:36px;
  background:linear-gradient(150deg,var(--green-800),var(--green-950));
  color:#fff;position:relative;overflow:hidden;
  box-shadow:var(--shadow-lg);
}
.hw-visual::before{
  content:'';position:absolute;width:300px;height:300px;border-radius:50%;
  background:radial-gradient(circle,rgba(163,230,53,.25),transparent 70%);
  top:-110px;right:-90px;
}
.hw-visual h3{font-size:1.35rem;font-weight:800;letter-spacing:-.03em;margin-bottom:8px;position:relative}
.hw-visual > p{font-size:.88rem;color:rgba(255,255,255,.72);margin-bottom:26px;position:relative}
.hw-nodes{display:flex;flex-direction:column;gap:0;position:relative}
.node{
  display:flex;align-items:center;gap:14px;padding:14px 0;position:relative;
}
.node:not(:last-child)::after{
  content:'';position:absolute;left:19px;top:52px;bottom:-6px;width:2px;
  background:linear-gradient(180deg,var(--lime),rgba(163,230,53,.15));
}
.node .nd{
  width:40px;height:40px;border-radius:12px;flex-shrink:0;display:grid;place-items:center;
  background:rgba(255,255,255,.09);border:1px solid rgba(163,230,53,.35);color:var(--lime);
  backdrop-filter:blur(4px);
}
.node b{display:block;font-size:.88rem;font-weight:800;letter-spacing:-.01em}
.node small{font-size:.74rem;color:rgba(255,255,255,.62);font-weight:500}

/* ============ BENEFITS ============ */
.benefits{background:linear-gradient(180deg,var(--green-50),#fff)}
.b-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.b-card{
  background:#fff;border-radius:var(--radius-lg);padding:30px 26px;
  border:1px solid var(--line);box-shadow:var(--shadow-sm);transition:.28s;
}
.b-card:hover{transform:translateY(-5px);box-shadow:var(--shadow);border-color:var(--green-200)}
.b-card .big{
  font-size:2.1rem;font-weight:800;letter-spacing:-.05em;color:var(--green-600);
  line-height:1;margin-bottom:10px;
}
.b-card h4{font-size:1rem;font-weight:800;color:var(--green-950);letter-spacing:-.02em;margin-bottom:8px}
.b-card p{font-size:.87rem;color:var(--muted);line-height:1.65}

/* ============ DOWNLOAD / APK ============ */
.download{padding:0 0 92px}
.dl-box{
  position:relative;overflow:hidden;
  border-radius:36px;padding:64px 56px;
  background:linear-gradient(135deg,var(--green-700) 0%,var(--green-800) 45%,var(--green-950) 100%);
  color:#fff;box-shadow:var(--shadow-lg);
  display:grid;grid-template-columns:1.15fr .85fr;gap:48px;align-items:center;
}
.dl-box::before{
  content:'';position:absolute;width:520px;height:520px;border-radius:50%;
  background:radial-gradient(circle,rgba(163,230,53,.22),transparent 68%);
  top:-220px;right:-160px;
}
.dl-box::after{
  content:'';position:absolute;inset:0;opacity:.09;pointer-events:none;
  background-image:radial-gradient(#fff 1px,transparent 1px);
  background-size:26px 26px;
}
.dl-content,.dl-side{position:relative;z-index:2}
.dl-content .eyebrow{color:var(--lime)}
.dl-content h2{
  font-size:clamp(1.8rem,3.6vw,2.5rem);line-height:1.12;
  letter-spacing:-.038em;font-weight:800;margin-bottom:16px;
}
.dl-content p{color:rgba(255,255,255,.8);font-size:1.02rem;margin-bottom:32px;max-width:520px}

.btn-apk{
  background:#fff;color:var(--green-800);
  padding:18px 34px;font-size:1.05rem;font-weight:800;
  box-shadow:0 14px 32px -10px rgba(0,0,0,.45);
  animation:glow 3s ease-in-out infinite;
}
@keyframes glow{
  0%,100%{box-shadow:0 14px 32px -10px rgba(0,0,0,.45),0 0 0 0 rgba(163,230,53,.5)}
  50%{box-shadow:0 14px 32px -10px rgba(0,0,0,.45),0 0 0 14px rgba(163,230,53,0)}
}
.btn-apk:hover{transform:translateY(-3px) scale(1.02);background:var(--lime);color:var(--green-950)}

.dl-note{
  display:flex;align-items:center;gap:9px;margin-top:18px;
  font-size:.8rem;color:rgba(255,255,255,.66);font-weight:600;
}

.dl-side{
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);
  border-radius:22px;padding:26px;backdrop-filter:blur(10px);
}
.dl-side h4{
  font-size:.74rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
  color:var(--lime);margin-bottom:18px;
}
.dl-side ul{list-style:none;display:flex;flex-direction:column;gap:13px}
.dl-side li{display:flex;align-items:flex-start;gap:11px;font-size:.86rem;color:rgba(255,255,255,.9);font-weight:600}
.dl-side li svg{flex-shrink:0;color:var(--lime);margin-top:2px}
.dl-side li span{color:rgba(255,255,255,.6);font-weight:500}

/* ============ FAQ ============ */
.faq{background:#fff}
.faq-list{max-width:800px;margin:0 auto;display:flex;flex-direction:column;gap:12px}
.faq-item{
  border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;
  background:#fff;transition:.25s;
}
.faq-item.open{border-color:var(--green-300);box-shadow:var(--shadow-sm);background:var(--green-50)}
.faq-q{
  width:100%;display:flex;align-items:center;justify-content:space-between;gap:18px;
  padding:20px 24px;text-align:left;font-weight:700;font-size:.98rem;
  color:var(--green-950);letter-spacing:-.015em;
}
.faq-q .chev{
  flex-shrink:0;width:28px;height:28px;border-radius:9px;display:grid;place-items:center;
  background:var(--green-100);color:var(--green-700);transition:.3s;
}
.faq-item.open .chev{transform:rotate(180deg);background:var(--green-600);color:#fff}
.faq-a{max-height:0;overflow:hidden;transition:max-height .35s ease}
.faq-a p{padding:0 24px 22px;font-size:.9rem;color:var(--muted);line-height:1.7}

/* ============ FOOTER ============ */
footer{
  background:var(--green-950);color:rgba(255,255,255,.7);
  padding:64px 0 32px;
}
.f-top{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:44px;padding-bottom:44px;border-bottom:1px solid rgba(255,255,255,.1)}
.f-brand .logo{color:#fff;margin-bottom:16px}
.f-brand p{font-size:.87rem;line-height:1.7;max-width:310px;color:rgba(255,255,255,.6)}
.f-col h5{
  font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
  color:var(--lime);margin-bottom:18px;
}
.f-col a{display:block;font-size:.87rem;padding:5px 0;color:rgba(255,255,255,.68);transition:.2s;font-weight:500}
.f-col a:hover{color:var(--lime);transform:translateX(3px)}
.f-bottom{
  display:flex;align-items:center;justify-content:space-between;gap:20px;
  padding-top:28px;font-size:.8rem;flex-wrap:wrap;
}
.f-bottom span{color:rgba(255,255,255,.5)}
.f-bottom .made{display:flex;align-items:center;gap:7px}

/* ============ REVEAL ANIM ============ */
.reveal{opacity:0;transform:translateY(26px);transition:opacity .7s cubic-bezier(.4,0,.2,1),transform .7s cubic-bezier(.4,0,.2,1)}
.reveal.in{opacity:1;transform:none}

/* ============ RESPONSIVE ============ */
@media (max-width:1024px){
  .hero-grid{grid-template-columns:1fr;gap:56px;text-align:center}
  .hero p.lede{margin-inline:auto}
  .hero-actions,.hero-meta{justify-content:center}
  .pill{margin-inline:auto}
  .f-grid,.b-grid{grid-template-columns:repeat(2,1fr)}
  .steps{grid-template-columns:repeat(2,1fr);gap:36px 20px}
  .steps::before{display:none}
  .hw-grid{grid-template-columns:1fr;gap:40px}
  .dl-box{grid-template-columns:1fr;padding:48px 34px;gap:36px}
  .f-top{grid-template-columns:1fr 1fr;gap:34px}
}

@media (max-width:760px){
  section{padding:68px 0}
  .nav-links{
    position:absolute;top:76px;left:0;right:0;
    flex-direction:column;align-items:stretch;gap:4px;
    background:#fff;border-bottom:1px solid var(--line);
    padding:16px 24px 24px;
    box-shadow:0 16px 30px -14px rgba(6,78,59,.2);
    transform:translateY(-12px);opacity:0;pointer-events:none;transition:.25s;
  }
  .nav-links.open{transform:none;opacity:1;pointer-events:auto}
  .nav-links a{padding:13px 14px;font-size:.98rem}
  .burger{display:grid}
  .nav-cta .btn{display:none}

  .hero{padding:52px 0 68px}
  .hero h1{font-size:clamp(2rem,8.6vw,2.7rem)}
  .hero p.lede{font-size:1rem}
  .hero-actions .btn{width:100%}
  .hero-meta{gap:14px;justify-content:center;font-size:.8rem}

  .phone-shot{width:250px}
  .phone{width:255px;height:520px;border-radius:38px}
  .float-badge.a{left:-8px;top:6%}
  .float-badge.b{right:-10px;bottom:10%}
  .float-badge{padding:9px 13px}

  .strip-grid{grid-template-columns:repeat(2,1fr);gap:26px 12px}
  .f-grid,.b-grid,.steps{grid-template-columns:1fr}
  .f-card{padding:26px 22px}
  .dl-box{padding:40px 24px;border-radius:26px}
  .btn-apk{width:100%;padding:17px 24px}
  .f-top{grid-template-columns:1fr;gap:32px}
  .f-bottom{flex-direction:column;text-align:center}
  .faq-q{padding:17px 18px;font-size:.92rem}
  .faq-a p{padding:0 18px 20px}
}

@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none!important;transition:none!important}
  .reveal{opacity:1;transform:none}
  html{scroll-behavior:auto}
}
</style>
</head>
<body>

<!-- ================= HEADER ================= -->
<header id="header">
  <div class="wrap">
    <nav class="nav">
      <a href="#" class="logo">
        <span class="logo-mark">
          <!-- Replace src with your logo file -->
          <img src="images/sagip-logo.png" alt="SAGIPfarmer"
               onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/>
            <path d="M9.2 14.6a2.8 2.8 0 0 0 2.8 2.8" opacity=".85"/>
          </svg>
        </span>
        <span class="logo-text">
          SAGIPfarmer
          <small>Smart Rice Monitoring</small>
        </span>
      </a>

      <div class="nav-links" id="navLinks">
        <a href="#features">Features</a>
        <a href="#how">How It Works</a>
        <a href="#hardware">System</a>
        <a href="#benefits">Benefits</a>
        <a href="#faq">FAQ</a>
      </div>

      <div class="nav-cta">
        <a href="#download" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
          </svg>
          Download APK
        </a>
        <button class="burger" id="burger" aria-label="Toggle menu" aria-expanded="false"><span></span></button>
      </div>
    </nav>
  </div>
</header>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="wrap">
    <div class="hero-grid">

      <div class="hero-copy">
        <span class="pill"><b>IoT + Solar</b> Built for Filipino rice farmers</span>

        <h1>Never guess your <span class="hl">soil moisture & water level</span> again.</h1>

        <p class="lede">
          SAGIPfarmer is a <strong>solar-powered IoT system</strong> that reads your rice field's
          water level and soil moisture in real time — and sends it straight to your Android phone.
          Know exactly when to irrigate, when to drain, and when to save water.
        </p>

        <div class="hero-actions">
          <a href="#download" class="btn btn-primary">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
            </svg>
            Download APK
          </a>
          <a href="#how" class="btn btn-ghost">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/>
            </svg>
            See how it works
          </a>
        </div>

        <div class="hero-meta">
          <div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Free to install
          </div>
          <div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Android 7.0+
          </div>
          <!-- <div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Works offline
          </div> -->
        </div>
      </div>

      <!-- Phone stage -->
      <div class="phone-stage">

        <!-- PRIMARY: phone image (replace src with your own) -->
        <div class="phone-shot">
          <img src="images/sagip-phone.png"
               alt="SAGIPfarmer mobile app displayed on an Android phone"
               onerror="this.closest('.phone-shot').style.display='none';document.getElementById('phoneFallback').style.display='block'">
        </div>

        <!-- FALLBACK: CSS-drawn phone (only shown if image fails) -->
        <div id="phoneFallback">
          <div class="phone">
            <div class="notch"></div>
            <div class="screen">
              <div class="app-bar">
                <div>
                  <div class="t">Good morning, Mang Ben</div>
                  <div class="n">Field 01 · Block A</div>
                </div>
                <div class="live"><i></i> LIVE</div>
              </div>

              <div class="app-body">
                <div class="gauge-row">
                  <div class="gauge-card">
                    <div class="gauge" style="--p:68;--gc:#16a34a">
                      <span>4.2<em>cm</em></span>
                    </div>
                    <div class="gauge-label">Water Level</div>
                  </div>
                  <div class="gauge-card">
                    <div class="gauge" style="--p:54;--gc:#0ea5e9">
                      <span>54<em>%</em></span>
                    </div>
                    <div class="gauge-label">Soil Moisture</div>
                  </div>
                </div>

                <div class="stat-list">
                  <div class="stat-row">
                    <div class="ic">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    </div>
                    <div class="txt"><b>Solar Battery</b><small>Charging</small></div>
                    <div class="val">92%</div>
                  </div>

                  <div class="stat-row">
                    <div class="ic">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1" fill="currentColor"/></svg>
                    </div>
                    <div class="txt"><b>Signal</b><small>LoRa → Gateway</small></div>
                    <div class="val">Strong</div>
                  </div>

                  <div class="stat-row">
                    <div class="ic">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/></svg>
                    </div>
                    <div class="txt"><b>Last Reading</b><small>2 minutes ago</small></div>
                    <div class="val">Now</div>
                  </div>
                </div>

                <div class="alert-chip">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                  Water level is 1.2 cm below target. Irrigate within 24 hours.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- floating badges -->
        <div class="float-badge a">
          <div class="ic">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2"/></svg>
          </div>
          <div><b>Solar Powered</b><small>No grid needed</small></div>
        </div>

        <div class="float-badge b">
          <div class="ic">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          </div>
          <div><b>Instant Alerts</b><small>Push notifications</small></div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= STATS STRIP ================= -->
<div class="strip">
  <div class="wrap">
    <div class="strip-grid">
      <div class="reveal">
        <div class="num">24/7</div>
        <div class="lbl">Continuous monitoring</div>
      </div>
      <div class="reveal">
        <div class="num">&lt; 5 min</div>
        <div class="lbl">Data refresh interval</div>
      </div>
      <div class="reveal">
        <div class="num">100%</div>
        <div class="lbl">Solar powered</div>
      </div>
      <div class="reveal">
        <div class="num">0</div>
        <div class="lbl">Manual field visits needed</div>
      </div>
    </div>
  </div>
</div>

<!-- ================= FEATURES ================= -->
<section class="features" id="features">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">Key Features</span>
      <h2>Everything you need to manage your rice field's water</h2>
      <p>Purpose-built for the realities of rice farming — low power, long range, and readable under the sun.</p>
    </div>

    <div class="f-grid">
      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/>
          </svg>
        </div>
        <h3>Real-Time Water Level</h3>
        <p>Ultrasonic sensors measure the exact water depth in your paddies every few minutes — no more walking the bunds to check.</p>
      </div>

      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s7-6.3 7-12A7 7 0 0 0 5 10c0 5.7 7 12 7 12z"/><circle cx="12" cy="10" r="2.4"/>
          </svg>
        </div>
        <h3>Soil Moisture Sensing</h3>
        <p>Capacitive probes buried at root depth track moisture percentage, so you irrigate based on the soil — not on habit.</p>
      </div>

      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
          </svg>
        </div>
        <h3>Solar-Powered Node</h3>
        <p>A small solar panel and rechargeable battery keep the field node running continuously — no wiring, no electric bill.</p>
      </div>

      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
          </svg>
        </div>
        <h3>Smart Threshold Alerts</h3>
        <p>Set your own minimum and maximum water levels. Get an instant push notification the moment readings go out of range.</p>
      </div>

      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/>
          </svg>
        </div>
        <h3>History &amp; Trends</h3>
        <p>Daily, weekly and seasonal graphs show how water and moisture behave — useful for planning the next cropping cycle.</p>
      </div>

      <div class="f-card reveal">
        <div class="f-ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1" fill="currentColor"/>
          </svg>
        </div>
        <h3>ESP Module</h3>
        <p>ESP-based Wi-Fi provides wireless connectivity between the field node and the local network, enabling reliable data transmission within the available Wi-Fi range.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="how" id="how">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">How It Works</span>
      <h2>From the paddy to your pocket in four steps</h2>
      <p>No technical background required. Install the node, open the app, and start reading your field.</p>
    </div>

    <div class="steps">
      <div class="step reveal">
        <div class="dot">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M12 11v5M9.5 13.5h5"/>
          </svg>
          <span class="n">1</span>
        </div>
        <h4>Install the Node</h4>
        <p>Mount the solar-powered sensor node at the edge of your field and push the probes into the soil.</p>
      </div>

      <div class="step reveal">
        <div class="dot">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2"/>
          </svg>
          <span class="n">2</span>
        </div>
        <h4>Sensors Collect Data</h4>
        <p>Water level and soil moisture are sampled automatically and continuously, day and night.</p>
      </div>

      <div class="step reveal">
        <div class="dot">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1" fill="currentColor"/>
          </svg>
          <span class="n">3</span>
        </div>
        <h4>Data Goes Wireless</h4>
        <p>Readings travel over a long-range low-power link to the gateway, then up to the database.</p>
      </div>

      <div class="step reveal">
        <div class="dot">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>
          </svg>
          <span class="n">4</span>
        </div>
        <h4>Read It on Your Phone</h4>
        <p>Open the SAGIPfarmer app to see live values, trends, and alerts — anywhere you have a signal.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= HARDWARE ================= -->
<section class="hardware" id="hardware">
  <div class="wrap">
    <div class="hw-grid">

      <div class="reveal">
        <span class="eyebrow">The System</span>
        <h2 style="font-size:clamp(1.8rem,3.6vw,2.5rem);line-height:1.14;letter-spacing:-.038em;font-weight:800;color:var(--green-950);margin-bottom:16px;">
          Low-cost hardware, built to survive the field
        </h2>
        <p style="color:var(--muted);font-size:1rem;">
          Every component was chosen for durability, low power draw, and availability in local electronics markets —
          so the system stays affordable and repairable.
        </p>

        <div class="hw-list">
          <div class="hw-item">
            <div class="ic">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/></svg>
            </div>
            <div>
              <b>Ultrasonic Water Level Sensor</b>
              <p>Non-contact measurement from above the water surface — no corrosion, no clogging with mud or algae.</p>
            </div>
          </div>

          <div class="hw-item">
            <div class="ic">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-6.3 7-12A7 7 0 0 0 5 10c0 5.7 7 12 7 12z"/><circle cx="12" cy="10" r="2.4"/></svg>
            </div>
            <div>
              <b>Capacitive Soil Moisture Probe</b>
              <p>Corrosion-resistant sensing at root depth, giving stable readings across a full cropping season.</p>
            </div>
          </div>

          <div class="hw-item">
            <div class="ic">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 21h8M12 18v3"/></svg>
            </div>
            <div>
              <b>Microcontroller + LoRa Module</b>
              <p>Handles sampling and transmits data over long distances with very little power consumption.</p>
            </div>
          </div>

          <div class="hw-item">
            <div class="ic">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </div>
            <div>
              <b>Solar Panel + Li-ion Battery</b>
              <p>Charges through the day and keeps the node alive overnight and through cloudy weather.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="hw-visual reveal">
        <h3>Data Flow</h3>
        <p>How a single reading travels from your paddy to your screen.</p>

        <div class="hw-nodes">
          <div class="node">
            <div class="nd">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/></svg>
            </div>
            <div>
              <b>Field Sensors</b>
              <small>Water level + soil moisture sampled</small>
            </div>
          </div>

          <div class="node">
            <div class="nd">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 9h6v6H9z"/></svg>
            </div>
            <div>
              <b>Solar Node (ESP32)</b>
              <small>Processes and transmits the reading</small>
            </div>
          </div>

          <div class="node">
            <div class="nd">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1" fill="currentColor"/></svg>
            </div>
            <div>
              <b>Gateway</b>
              <small>Receives data, forwards to the cloud</small>
            </div>
          </div>

          <div class="node">
            <div class="nd">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.3A7 7 0 1 0 5 16.7"/><path d="M18 10a4 4 0 0 1 0 8h-6"/><path d="M12 18l-2-2 2-2" transform="translate(0,-1)"/></svg>
            </div>
            <div>
              <b>Online Database</b>
              <small>Stores readings, applies alert rules</small>
            </div>
          </div>

          <div class="node">
            <div class="nd">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></svg>
            </div>
            <div>
              <b>SAGIPfarmer App</b>
              <small>Live dashboard + push alerts</small>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= BENEFITS ================= -->
<section class="benefits" id="benefits">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">Why It Matters</span>
      <h2>Save water, save fuel, save the harvest</h2>
      <p>Water management is the single biggest controllable factor in rice yield. SAGIPfarmer makes it measurable.</p>
    </div>

    <div class="b-grid">
      <div class="b-card reveal">
        <div class="big">↓ 25%</div>
        <h4>Less Water Wasted</h4>
        <p>Irrigate only when the field actually needs it, instead of flooding on a fixed schedule.</p>
      </div>

      <div class="b-card reveal">
        <div class="big">↓ 30%</div>
        <h4>Lower Pumping Cost</h4>
        <p>Fewer unnecessary pumping hours means less diesel, less electricity, and less wear on equipment.</p>
      </div>

      <div class="b-card reveal">
        <div class="big">↑ Yield</div>
        <h4>Healthier Rice Growth</h4>
        <p>Maintaining the correct water depth at each growth stage supports tillering and grain filling.</p>
      </div>

      <div class="b-card reveal">
        <div class="big">0 km</div>
        <h4>No More Field Rounds</h4>
        <p>Check every plot from home. Stop walking the bunds at dawn just to read a water gauge.</p>
      </div>

      <div class="b-card reveal">
        <div class="big">24/7</div>
        <h4>Catches Night Problems</h4>
        <p>Breaches and sudden drainage happen at night too. Alerts reach you before the damage spreads.</p>
      </div>

      <div class="b-card reveal">
        <div class="big">∞</div>
        <h4>Free Sunlight</h4>
        <p>Solar power means the node works in fields far from any electrical connection — and costs nothing to run.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= DOWNLOAD ================= -->
<section class="download" id="download">
  <div class="wrap">
    <div class="dl-box">
      <div class="dl-content">
        <span class="eyebrow">Get the App</span>
        <h2>Download SAGIPfarmer for Android</h2>
        <p>
          Install the app directly on your phone. Pair it with your field node, set your water-level thresholds,
          and start receiving live readings in minutes.
        </p>

        <a href="/mobile_app/SAGIPfarmer.apk" download class="btn btn-apk" id="apkBtn">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
          </svg>
          Download APK
        </a>

        <div class="dl-note">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
          Free download · No ads · No in-app purchases
        </div>
      </div>

      <div class="dl-side">
        <h4>Requirements</h4>
        <ul>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <div>Android 7.0 (Nougat) or higher</div>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <div>~18 MB storage <span>· small footprint</span></div>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <div>2 GB RAM recommended</div>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <div>Internet or gateway connection</div>
          </li>
          <li>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <div>"Install from unknown sources" enabled</div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ================= FAQ ================= -->
<section class="faq" id="faq">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">FAQ</span>
      <h2>Common questions</h2>
    </div>

    <div class="faq-list">
      <div class="faq-item reveal">
        <button class="faq-q">
          Do I need electricity or Wi-Fi in the field?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>No. The field node runs entirely on its own solar panel and battery. Data is sent over a long-range low-power wireless link to a gateway, which is the only part that needs an internet connection. This means the sensors can be placed far from any power line or Wi-Fi router.</p></div>
      </div>

      <div class="faq-item reveal">
        <button class="faq-q">
          How accurate are the water level and moisture readings?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>The ultrasonic water level sensor is accurate to within roughly ±1 cm under normal field conditions, and the capacitive soil moisture probe gives stable relative readings across the season. Both are calibrated before deployment so the numbers in the app match what you would measure manually.</p></div>
      </div>

      <div class="faq-item reveal">
        <button class="faq-q">
          How do I install the APK on my phone?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>Tap the Download APK button, then open the downloaded file from your notification bar or Files app. Android will ask you to allow installation from this source — enable it, tap Install, and the app will appear on your home screen. Since it is installed outside the Play Store, you may see a standard "unknown app" prompt, which is normal.</p></div>
      </div>

      <div class="faq-item reveal">
        <button class="faq-q">
          Can one app monitor multiple rice fields?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>Yes. You can register several nodes and switch between plots from the dashboard. Each plot keeps its own thresholds, history, and alert settings, so a farmer managing multiple paddies can see everything in one place.</p></div>
      </div>

      <div class="faq-item reveal">
        <button class="faq-q">
          What happens if the internet goes down?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>The node keeps measuring and the gateway stores readings locally. Once the connection returns, the buffered data is uploaded automatically, so there are no gaps in your history. The app also caches the last known readings so you can still view them offline.</p></div>
      </div>

      <div class="faq-item reveal">
        <button class="faq-q">
          Is the system waterproof and durable?
          <span class="chev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </button>
        <div class="faq-a"><p>The electronics enclosure is IP65-rated and mounted above the expected flood line, with sealed cable glands. The soil probe is buried and the ultrasonic sensor is non-contact, which removes the two most common failure points in wet-field instrumentation.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="wrap">
    <div class="f-top">
      <div class="f-brand">
        <a href="#" class="logo">
          <span class="logo-mark">
            <!-- Replace src with your logo file -->
            <img src="images/sagip-logo.png" alt="SAGIPfarmer"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/>
              <path d="M9.2 14.6a2.8 2.8 0 0 0 2.8 2.8" opacity=".85"/>
            </svg>
          </span>
          <span class="logo-text">SAGIPfarmer<small>Smart Rice Monitoring</small></span>
        </a>
        <p>An IoT-based, solar-powered mobile application for real-time water level and soil moisture monitoring in rice fields.</p>
      </div>

      <div class="f-col">
        <h5>Product</h5>
        <a href="#features">Features</a>
        <a href="#how">How It Works</a>
        <a href="#hardware">System</a>
        <a href="#download">Download APK</a>
      </div>

      <div class="f-col">
        <h5>Learn</h5>
        <a href="#benefits">Benefits</a>
        <a href="#faq">FAQ</a>
        <a href="#how">Setup Guide</a>
      </div>

      <div class="f-col">
        <h5>Contact</h5>
        <a href="mailto:hello@sagipfarmer.ph">hello@sagipfarmer.ph</a>
        <a href="#">Support</a>
        <a href="#">Report an Issue</a>
      </div>
    </div>

    <div class="f-bottom">
      <span>© <span id="yr">2025</span> SAGIPfarmer. All rights reserved.</span>
      <span class="made">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a3e635" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5c0 0-6.5 7-6.5 11.5a6.5 6.5 0 0 0 13 0C18.5 9.5 12 2.5 12 2.5z"/></svg>
        Built for rice farmers
      </span>
    </div>
  </div>
</footer>

<script>
/* ---------- Year ---------- */
document.getElementById('yr').textContent = new Date().getFullYear();

/* ---------- Header shadow on scroll ---------- */
const header = document.getElementById('header');
const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 12);
onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

/* ---------- Mobile menu ---------- */
const burger = document.getElementById('burger');
const navLinks = document.getElementById('navLinks');
burger.addEventListener('click', () => {
  const open = navLinks.classList.toggle('open');
  burger.classList.toggle('open', open);
  burger.setAttribute('aria-expanded', String(open));
});
navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  navLinks.classList.remove('open');
  burger.classList.remove('open');
  burger.setAttribute('aria-expanded', 'false');
}));

/* ---------- Scroll reveal ---------- */
const io = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      e.target.style.transitionDelay = (i % 4) * 70 + 'ms';
      e.target.classList.add('in');
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(el => io.observe(el));

/* ---------- FAQ accordion ---------- */
document.querySelectorAll('.faq-item').forEach(item => {
  const q = item.querySelector('.faq-q');
  const a = item.querySelector('.faq-a');
  q.addEventListener('click', () => {
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(o => {
      o.classList.remove('open');
      o.querySelector('.faq-a').style.maxHeight = null;
    });
    if (!isOpen) {
      item.classList.add('open');
      a.style.maxHeight = a.scrollHeight + 'px';
    }
  });
});

/* ---------- Animate gauges (fallback phone only) ---------- */
const gaugeIO = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      document.querySelectorAll('.gauge').forEach((g, idx) => {
        const target = g.style.getPropertyValue('--p');
        g.style.setProperty('--p', 0);
        setTimeout(() => g.style.setProperty('--p', target), 180 * idx + 250);
      });
      gaugeIO.disconnect();
    }
  });
}, { threshold: 0.4 });
const firstGauge = document.querySelector('.gauge');
if (firstGauge) gaugeIO.observe(firstGauge);

/* ---------- APK download ---------- */
document.getElementById('apkBtn').addEventListener('click', function () {
  if (this.getAttribute('href') === 'SAGIPfarmer.apk') {
    console.log('SAGIPfarmer APK download started (placeholder path).');
  }
});
</script>

</body>
</html>