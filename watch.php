<?php
// SkillzUp - watch.php (Custom video player + chapter list)
require_once __DIR__ . '/common/config.php';
require_user();

$courseId = (int)($_GET['course'] ?? 0);
if (!is_purchased((int)current_user()['id'], $courseId)) { header('Location: /course_detail.php?id='.$courseId); exit; }

$chapters = db()->prepare('SELECT * FROM chapters WHERE course_id=? ORDER BY sort_order, id');
$chapters->execute([$courseId]);
$chapters = $chapters->fetchAll();

$videosByChapter = [];
foreach ($chapters as $ch) {
  $vs = db()->prepare('SELECT * FROM videos WHERE chapter_id=? ORDER BY sort_order, id');
  $vs->execute([$ch['id']]);
  $videosByChapter[$ch['id']] = $vs->fetchAll();
}
include __DIR__ . '/common/header.php';
?>
<h1 class="text-base font-semibold mb-3">Course Player</h1>

<div class="space-y-3">
  <div class="card rounded p-2">
    <div class="relative bg-black aspect-video rounded overflow-hidden">
      <video id="video" class="w-full h-full" preload="metadata" controlslist="nodownload noplaybackrate" oncontextmenu="return false"></video>
       Custom Controls 
      <div id="controls" class="absolute bottom-0 inset-x-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
        <div class="flex items-center gap-2">
          <button id="playBtn" class="p-2 rounded bg-white/10 text-white"><i class="fa-solid fa-play"></i></button>
          <input id="seek" type="range" min="0" max="100" value="0" class="flex-1 accent-sky-500">
          <div id="time" class="text-xs text-white w-20 text-right">00:00</div>
          <input id="vol" type="range" min="0" max="1" step="0.05" value="1" class="w-20 accent-sky-500">
          <button id="fsBtn" class="p-2 rounded bg-white/10 text-white"><i class="fa-solid fa-expand"></i></button>
        </div>
      </div>
    </div>
  </div>

  <div class="card rounded divide-y divide-slate-800">
    <?php foreach ($chapters as $ch): ?>
      <details class="group">
        <summary class="list-none cursor-pointer p-3 flex items-center justify-between">
          <span class="font-medium"><?= e($ch['title']) ?></span>
          <i class="fa-solid fa-chevron-down transition-transform group-open:rotate-180"></i>
        </summary>
        <div class="p-2 space-y-1">
          <?php foreach ($videosByChapter[$ch['id']] as $v): ?>
            <button class="w-full text-left p-2 rounded hover:bg-slate-800 text-sm video-item" data-src="<?= e($v['file_path']) ?>"><?= e($v['title']) ?></button>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
</div>

<script>
  const video = document.getElementById('video');
  const playBtn = document.getElementById('playBtn');
  const seek = document.getElementById('seek');
  const time = document.getElementById('time');
  const vol = document.getElementById('vol');
  const fsBtn = document.getElementById('fsBtn');

  const fmt = s => {
    s = Math.floor(s);
    const m = String(Math.floor(s/60)).padStart(2,'0');
    const ss = String(s%60).padStart(2,'0');
    return `${m}:${ss}`;
  };

  playBtn.addEventListener('click', () => {
    if (video.paused) { video.play(); playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>'; }
    else { video.pause(); playBtn.innerHTML = '<i class="fa-solid fa-play"></i>'; }
  });
  video.addEventListener('timeupdate', () => {
    seek.value = video.duration ? (video.currentTime / video.duration) * 100 : 0;
    time.textContent = fmt(video.currentTime);
  });
  seek.addEventListener('input', () => {
    if (video.duration) video.currentTime = (seek.value / 100) * video.duration;
  });
  vol.addEventListener('input', () => video.volume = vol.value);
  fsBtn.addEventListener('click', () => {
    if (document.fullscreenElement) document.exitFullscreen();
    else video.parentElement.requestFullscreen();
  });
  // Load first video by default
  const items = document.querySelectorAll('.video-item');
  items.forEach(btn => btn.addEventListener('click', () => {
    const src = btn.dataset.src;
    if (!src) return;
    video.src = src;
    video.play().catch(()=>{});
    playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
  }));
  if (items[0]) items[0].click();
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>
