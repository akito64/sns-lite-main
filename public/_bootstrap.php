<?php
// どのページからでも読み込む簡易レイアウト
?><!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SNS Lite</title>
<style>
    :root { --fg:#222; --muted:#666; --line:#e5e7eb; --bg:#fff; --prim:#2563eb; }
    *{box-sizing:border-box} body{margin:0;background:#fafafa;color:var(--fg);font:16px/1.6 system-ui, -apple-system, Segoe UI, Roboto, "Noto Sans JP", sans-serif}
    a{color:var(--prim);text-decoration:none} a:hover{text-decoration:underline}
    .topnav{position:sticky;top:0;background:#fff;border-bottom:1px solid var(--line);padding:.6rem .9rem;display:flex;gap:.6rem;justify-content:space-between;z-index:5}
    nav a{margin-left:.8rem}
    main{max-width:720px;margin:0 auto;padding:1rem}
    .card{background:var(--bg);border:1px solid var(--line);border-radius:.6rem;padding:1rem}
    .stack>*+*{margin-top:.9rem} .stack-xs>*+*{margin-top:.4rem}
    .row{display:flex;flex-wrap:wrap} .vcenter{align-items:center} .gap-s{gap:.6rem}
    .grid{display:grid} .grid-1{grid-template-columns:1fr} .grid-2{grid-template-columns:1fr 1fr}
    .gap-xs{gap:.4rem}
    .input{width:100%;padding:.6rem;border:1px solid var(--line);border-radius:.5rem;background:#fff}
    .btn{display:inline-block;background:var(--prim);color:#fff;border:none;padding:.6rem 1rem;border-radius:.5rem}
    .btn:active{opacity:.9}
    .avatar{width:28px;height:28px;border-radius:50%;object-fit:cover}
    .postimg{width:100%;height:auto;border-radius:.5rem}
    .thumb{width:100%;height:auto;border:1px solid var(--line);border-radius:.4rem}
    .muted{color:var(--muted)} .bold{font-weight:600}
    @media (min-width:768px){ main{padding:1.25rem} }

    .imgs { display:grid; gap:.5rem; }
    .imgs img { width:100%; display:block; border-radius:.5rem; }


    .imgs.n1 { grid-template-columns: 1fr; }
    .imgs.n1 img { height:auto; max-height:65vh; object-fit:contain; }


    .imgs.n2 { grid-template-columns: repeat(2,1fr); }
    .imgs.n3 { grid-template-columns: repeat(2,1fr); }
    .imgs.n4 { grid-template-columns: repeat(2,1fr); }

    .imgs.n2 img,
    .imgs.n3 img,
    .imgs.n4 img {
    aspect-ratio: 1 / 1;
    height:auto;
    object-fit: cover; 
    }


    #thumbs { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; }
    #thumbs img.thumb{
    width:100%;
    aspect-ratio:1/1;
    object-fit:cover;
    border-radius:.5rem;
    border:1px solid var(--line);
    }


    @media (max-width: 520px){
    .imgs { gap:.4rem; }
    }


</style>
</head>
<body>
<main>
