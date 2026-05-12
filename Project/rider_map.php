<?php
function renderRiderMap(bool $large = false): void {
    $h = $large ? '380px' : '240px';
    echo <<<HTML
<div class="rider-map-container" style="height:{$h}">
    <div class="rider-map-grid"></div>

    <div class="rider-map-roads">
        <svg viewBox="0 0 600 380" preserveAspectRatio="none">
            
            <line x1="0" y1="95"  x2="600" y2="95"  class="road-line"/>
            <line x1="0" y1="95"  x2="600" y2="95"  class="road-center"/>
            <line x1="0" y1="210" x2="600" y2="210" class="road-line"/>
            <line x1="0" y1="210" x2="600" y2="210" class="road-center"/>
            <line x1="0" y1="320" x2="600" y2="320" class="road-line"/>
            
            <line x1="140" y1="0" x2="140" y2="380" class="road-line"/>
            <line x1="140" y1="0" x2="140" y2="380" class="road-center"/>
            <line x1="350" y1="0" x2="350" y2="380" class="road-line"/>
            <line x1="500" y1="0" x2="500" y2="380" class="road-line"/>
        </svg>
    </div>

    <!-- Route dashed line from restaurant → rider → customer -->
    <div class="rider-route-line">
        <svg viewBox="0 0 600 380" preserveAspectRatio="none">
            <polyline points="140,240 140,210 350,210 350,95 490,95"
                stroke="#f39c12" stroke-width="3" fill="none"
                stroke-dasharray="12 8" stroke-linecap="round"
                style="animation: dashMove 1.2s linear infinite;stroke-dashoffset:0"/>
        </svg>
    </div>

    <div style="position:absolute;left:140px;top:240px;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#3498db,#2980b9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;border:3px solid white;box-shadow:0 4px 14px rgba(52,152,219,0.5)">🍽️</div>
    </div>

    <div class="rider-pin" style="left:52%;top:55%">
        <div style="position:relative">
            <div class="rider-pulse"></div>
            <div class="rider-pin-icon"></div>
        </div>
        <div class="rider-pin-shadow"></div>
    </div>

    <div style="position:absolute;left:490px;top:95px;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#2ecc71,#27ae60);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;border:3px solid white;box-shadow:0 4px 14px rgba(46,204,113,0.5)">🏠</div>
    </div>

    <div class="rider-map-overlay">
        <div class="rider-map-badge"><span class="dot"></span> Rahul — En Route</div>
        <div class="rider-map-badge">⏱ ~12 min away</div>
    </div>
</div>

<style>
@keyframes dashMove { to { stroke-dashoffset: -40; } }
</style>
HTML;
}
