<style>
.trout-container {
    width: 400px;
    height: 200px;
    position: relative;
    margin: 40px auto;
}

.trout-body {
    position: absolute;
    left: 60px;
    top: 60px;
    width: 220px;
    height: 60px;
    background: linear-gradient(90deg, #b2e0e6 60%, #7fc8a9 100%);
    border-radius: 60px 120px 120px 60px / 50px 50px 50px 50px;
    box-shadow: 0 2px 12px #7fc8a9 inset;
    z-index: 2;
}

.trout-tail {
    position: absolute;
    left: 260px;
    top: 75px;
    width: 60px;
    height: 40px;
    background: linear-gradient(120deg, #7fc8a9 60%, #b2e0e6 100%);
    clip-path: polygon(0 0, 100% 50%, 0 100%);
    z-index: 1;
    opacity: 0.95;
}

.trout-fin-top {
    position: absolute;
    left: 120px;
    top: 45px;
    width: 50px;
    height: 30px;
    background: #e6d3b2;
    border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    transform: rotate(-10deg);
    z-index: 3;
    opacity: 0.8;
}

.trout-fin-bottom {
    position: absolute;
    left: 130px;
    top: 110px;
    width: 40px;
    height: 20px;
    background: #e6d3b2;
    border-radius: 0 0 50% 50% / 0 0 100% 100%;
    transform: rotate(10deg);
    z-index: 3;
    opacity: 0.7;
}

.trout-fin-side {
    position: absolute;
    left: 110px;
    top: 90px;
    width: 30px;
    height: 18px;
    background: #e6d3b2;
    border-radius: 50% 50% 50% 50% / 60% 60% 100% 100%;
    transform: rotate(-20deg);
    z-index: 4;
    opacity: 0.7;
}

.trout-eye {
    position: absolute;
    left: 75px;
    top: 80px;
    width: 16px;
    height: 16px;
    background: #fff;
    border-radius: 50%;
    border: 2px solid #888;
    z-index: 5;
}

.trout-eye-pupil {
    position: absolute;
    left: 82px;
    top: 86px;
    width: 6px;
    height: 6px;
    background: #222;
    border-radius: 50%;
    z-index: 6;
}

.trout-mouth {
    position: absolute;
    left: 60px;
    top: 98px;
    width: 18px;
    height: 8px;
    border-bottom: 3px solid #b97a56;
    border-radius: 0 0 18px 18px;
    z-index: 7;
}

.trout-spot {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #b97a56;
    border-radius: 50%;
    opacity: 0.7;
    z-index: 8;
}
.trout-spot.s1 { left: 120px; top: 85px; }
.trout-spot.s2 { left: 150px; top: 75px; }
.trout-spot.s3 { left: 180px; top: 95px; }
.trout-spot.s4 { left: 210px; top: 80px; }
.trout-spot.s5 { left: 170px; top: 105px; }
.trout-spot.s6 { left: 200px; top: 100px; }
</style>

<div class="trout-container">
    <div class="trout-body"></div>
    <div class="trout-tail"></div>
    <div class="trout-fin-top"></div>
    <div class="trout-fin-bottom"></div>
    <div class="trout-fin-side"></div>
    <div class="trout-eye"></div>
    <div class="trout-eye-pupil"></div>
    <div class="trout-mouth"></div>
    <div class="trout-spot s1"></div>
    <div class="trout-spot s2"></div>
    <div class="trout-spot s3"></div>
    <div class="trout-spot s4"></div>
    <div class="trout-spot s5"></div>
    <div class="trout-spot s6"></div>
</div>

