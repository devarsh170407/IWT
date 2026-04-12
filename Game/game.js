// ═══════════════════════════════════════════════════════════════════
//  UNO  –  Full Game Logic
// ═══════════════════════════════════════════════════════════════════

// ─── Constants ──────────────────────────────────────────────────────────────
const COLORS  = ['red', 'green', 'blue', 'yellow'];
const NUMBERS = ['0','1','2','3','4','5','6','7','8','9'];
const ACTIONS = ['Skip','Reverse','Draw Two'];
const WILDS   = ['Wild','Wild Draw Four'];

// ─── Build deck ─────────────────────────────────────────────────────────────
function buildDeck() {
  const deck = [];
  COLORS.forEach(color => {
    // 0 once, 1‑9 twice, actions twice
    deck.push({ color, value: '0', type: 'number' });
    for (let i = 1; i <= 9; i++) {
      deck.push({ color, value: String(i), type: 'number' });
      deck.push({ color, value: String(i), type: 'number' });
    }
    ACTIONS.forEach(a => {
      deck.push({ color, value: a, type: 'action' });
      deck.push({ color, value: a, type: 'action' });
    });
  });
  // 4 of each wild
  for (let i = 0; i < 4; i++) {
    deck.push({ color: 'wild', value: 'Wild',          type: 'wild' });
    deck.push({ color: 'wild', value: 'Wild Draw Four', type: 'wild' });
  }
  return shuffle(deck);
}

function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

// ─── State ──────────────────────────────────────────────────────────────────
let G = {};   // game state

function initGame() {
  const deck = buildDeck();
  // deal 7 each
  const humanHand = deck.splice(0, 7);
  const aiHand    = deck.splice(0, 7);

  // find first non‑wild starting card
  let startIdx = deck.findIndex(c => c.type !== 'wild');
  if (startIdx < 0) startIdx = 0;
  const [startCard] = deck.splice(startIdx, 1);

  G = {
    deck,
    discard:      [startCard],
    human:        humanHand,
    ai:           aiHand,
    currentColor: startCard.color,
    turn:         'human',   // 'human' | 'ai'
    direction:    1,         // +1 normal, -1 reversed (only matters with ≥3 players but tracked)
    locked:       false,
    pendingWild:  null,
    cardsPlayed:  0,
    cardsDrawn:   0,
    unoSaid:      { human: false, ai: false },
    gameOver:     false,
  };

  renderAll();
  setLog('Your turn — play a matching card or draw!');
  updateTurnIndicator();
}

// ─── Render ──────────────────────────────────────────────────────────────────
function renderAll() {
  renderHumanHand();
  renderAIHand();
  renderDiscard();
  updateCounts();
  updateProgress();
  updateUnoBadges();
}

/* Discard pile (top card) */
function renderDiscard() {
  const top = G.discard[G.discard.length - 1];
  const el  = document.getElementById('discardPile');
  el.className = `card ${top.color}`;
  el.innerHTML = cardInnerHTML(top);

  const ring = document.getElementById('colorRing');
  ring.className = `current-color-ring ${G.currentColor}`;
}

/* Human hand */
function renderHumanHand() {
  const container = document.getElementById('humanHand');
  container.innerHTML = '';
  const isYourTurn = G.turn === 'human' && !G.locked;

  G.human.forEach((card, i) => {
    const el = document.createElement('div');
    el.className = `card ${card.color}`;
    if (isYourTurn && canPlay(card)) el.classList.add('playable');
    el.innerHTML = cardInnerHTML(card);
    el.dataset.index = i;
    if (isYourTurn && canPlay(card)) {
      el.addEventListener('click', () => humanPlayCard(i));
    }
    container.appendChild(el);
  });

  // UNO button visibility
  const unoBtn = document.getElementById('unoBtn');
  if (G.human.length === 1 && !G.unoSaid.human) unoBtn.classList.remove('hidden');
  else unoBtn.classList.add('hidden');
}

/* AI hand (face‑down) */
function renderAIHand() {
  const container = document.getElementById('aiHand');
  container.innerHTML = '';
  G.ai.forEach(() => {
    const el = document.createElement('div');
    el.className = 'ai-hand-card';
    container.appendChild(el);
  });
}

/* Card inner HTML */
function cardInnerHTML(card) {
  const pip = cardPip(card);
  return `
    <span class="corner-pip tl">${pip}</span>
    <span class="card-value">${cardSymbol(card)}</span>
    ${card.type !== 'wild' ? `<span class="card-type">${card.color}</span>` : ''}
    <span class="corner-pip br">${pip}</span>`;
}
function cardPip(card) {
  if (card.type === 'number') return card.value;
  const map = { 'Skip': '🚫', 'Reverse': '🔄', 'Draw Two': '+2', 'Wild': '🌈', 'Wild Draw Four': '+4' };
  return map[card.value] || '?';
}
function cardSymbol(card) {
  const map = { 'Skip': '🚫', 'Reverse': '🔄', 'Draw Two': '+2', 'Wild': '🌈', 'Wild Draw Four': '+4' };
  return card.type === 'number' ? card.value : (map[card.value] || card.value);
}

function updateCounts() {
  document.getElementById('humanCount').textContent = `${G.human.length} card${G.human.length !== 1 ? 's' : ''}`;
  document.getElementById('aiCount').textContent    = `${G.ai.length} card${G.ai.length !== 1 ? 's' : ''}`;
}
function updateProgress() { /* could show draw pile size */ }
function updateUnoBadges() {
  document.getElementById('humanUnoBadge').classList.toggle('hidden', G.human.length !== 1);
  document.getElementById('aiUnoBadge').classList.toggle('hidden',    G.ai.length !== 1);
}

// ─── Can play? ───────────────────────────────────────────────────────────────
function canPlay(card) {
  const top = G.discard[G.discard.length - 1];
  if (card.type === 'wild') return true;
  if (card.color === G.currentColor) return true;
  if (card.type === 'number' && top.type === 'number' && card.value === top.value) return true;
  if (card.type === 'action' && top.type === 'action' && card.value === top.value) return true;
  return false;
}

// ─── Human plays a card ──────────────────────────────────────────────────────
function humanPlayCard(index) {
  if (G.locked || G.turn !== 'human' || G.gameOver) return;
  const card = G.human[index];
  if (!canPlay(card)) return;

  G.human.splice(index, 1);
  G.cardsPlayed++;

  if (card.type === 'wild') {
    G.pendingWild = card;
    showColorChooser();
    return;
  }

  playCard(card, 'human');
}

/* Draw card action */
document.getElementById('drawPile').addEventListener('click', () => {
  if (G.locked || G.turn !== 'human' || G.gameOver) return;
  humanDraw();
});

function humanDraw() {
  const drawn = drawCard();
  if (!drawn) return;
  G.cardsDrawn++;
  G.human.push(drawn);
  setLog(`You drew a card.`);
  G.unoSaid.human = false;

  if (canPlay(drawn)) {
    setLog(`You drew — and can play it!`);
    renderAll();
    // allow play or pass
    G.locked = false;
    renderHumanHand();
  } else {
    setLog(`You drew — no playable card, turn passes.`);
    renderAll();
    endTurn('human');
  }
}

// ─── Color chooser ───────────────────────────────────────────────────────────
function showColorChooser() {
  document.getElementById('colorChooser').classList.remove('hidden');
}
function hideColorChooser() {
  document.getElementById('colorChooser').classList.add('hidden');
}

document.querySelectorAll('.color-swatch').forEach(btn => {
  btn.addEventListener('click', () => {
    const chosen = btn.dataset.color;
    hideColorChooser();
    const card = G.pendingWild;
    G.pendingWild = null;
    G.currentColor = chosen;
    playCard(card, 'human');
  });
});

// ─── Play a card (shared) ────────────────────────────────────────────────────
function playCard(card, who) {
  G.discard.push(card);
  if (card.type !== 'wild') G.currentColor = card.color;

  const opponent = who === 'human' ? 'ai' : 'human';
  const whoName  = who === 'human' ? 'You' : 'AI';

  setLog(`${whoName} played ${cardPip(card)} ${card.type !== 'wild' ? card.color : ''}`);
  renderAll();

  // Check win
  if (G[who].length === 0) { endGame(who); return; }

  // Handle action / wild effects
  let skipOpponent = false;

  if (card.value === 'Skip') {
    skipOpponent = true;
    setLog(`${opponent === 'human' ? 'Your' : "AI's"} turn is skipped!`);
  } else if (card.value === 'Reverse') {
    G.direction *= -1;
    document.getElementById('directionIndicator').textContent = G.direction === 1 ? '▶ Normal' : '◀ Reversed';
    // In 2‑player, Reverse acts like Skip
    skipOpponent = true;
    setLog(`Order reversed — ${whoName} plays again!`);
  } else if (card.value === 'Draw Two') {
    forceDraw(opponent, 2);
    setLog(`${opponent === 'human' ? 'You draw' : 'AI draws'} 2 cards!`);
    skipOpponent = true;
  } else if (card.value === 'Wild Draw Four') {
    forceDraw(opponent, 4);
    setLog(`${opponent === 'human' ? 'You draw' : 'AI draws'} 4 cards! Color: ${G.currentColor}`);
    skipOpponent = true;
  } else if (card.value === 'Wild') {
    setLog(`Wild! Color changed to ${G.currentColor}.`);
  }

  if (skipOpponent) {
    endTurn(opponent);   // opponent skipped → same player goes again
  } else {
    endTurn(who);        // normal: next player
  }
}

// ─── End turn ────────────────────────────────────────────────────────────────
function endTurn(justPlayed) {
  // Switch turn
  G.turn = justPlayed === 'human' ? 'ai' : 'human';
  updateTurnIndicator();
  renderHumanHand();

  if (G.turn === 'ai') {
    G.locked = true;
    setTimeout(aiTurn, 1200);
  } else {
    G.locked = false;
  }
}

function updateTurnIndicator() {
  const el = document.getElementById('turnIndicator');
  if (G.turn === 'human') {
    el.textContent = '✅ Your Turn';
    el.className = 'turn-indicator your-turn';
  } else {
    el.textContent = '🤖 AI Thinking…';
    el.className = 'turn-indicator ai-turn';
  }
}

// ─── AI turn ─────────────────────────────────────────────────────────────────
function aiTurn() {
  if (G.gameOver) return;

  const playable = G.ai.filter(c => canPlay(c));

  if (playable.length === 0) {
    // Draw
    const drawn = drawCard();
    if (drawn) {
      G.ai.push(drawn);
      setLog('AI draws a card…');
      renderAll();
      if (canPlay(drawn)) {
        setTimeout(() => aiPlayCard(drawn), 700);
        return;
      }
    }
    setLog('AI draws and passes.');
    endTurn('ai');
    return;
  }

  // Smart selection: prefer number → action → wild
  let chosen = playable.find(c => c.type === 'number') ||
               playable.find(c => c.type === 'action')  ||
               playable[0];

  // Remove from hand
  const idx = G.ai.indexOf(chosen);
  G.ai.splice(idx, 1);

  if (chosen.type === 'wild') {
    // AI picks the color it has the most of
    const colorCounts = {};
    COLORS.forEach(c => colorCounts[c] = G.ai.filter(x => x.color === c).length);
    const best = COLORS.reduce((a, b) => colorCounts[a] >= colorCounts[b] ? a : b);
    G.currentColor = best;
    setLog(`AI plays Wild → changes to ${best}!`);
  }

  G.cardsPlayed++;
  // UNO check
  if (G.ai.length === 1) {
    G.unoSaid.ai = true;
    setLog('🤖 AI says UNO!');
  }

  playCard(chosen, 'ai');
}

function aiPlayCard(card) {
  const idx = G.ai.indexOf(card);
  if (idx > -1) G.ai.splice(idx, 1);
  if (card.type === 'wild') {
    const colorCounts = {};
    COLORS.forEach(c => colorCounts[c] = G.ai.filter(x => x.color === c).length);
    const best = COLORS.reduce((a, b) => colorCounts[a] >= colorCounts[b] ? a : b);
    G.currentColor = best;
  }
  G.cardsPlayed++;
  playCard(card, 'ai');
}

// ─── Force draw ──────────────────────────────────────────────────────────────
function forceDraw(who, count) {
  for (let i = 0; i < count; i++) {
    const c = drawCard();
    if (c) G[who].push(c);
  }
  if (who === 'human') G.cardsDrawn += count;
}

// ─── Draw from deck (reshuffle discard if needed) ────────────────────────────
function drawCard() {
  if (G.deck.length === 0) {
    if (G.discard.length <= 1) return null;
    const top = G.discard.pop();
    G.deck = shuffle(G.discard);
    G.discard = [top];
    setLog('Deck reshuffled from discard pile!');
  }
  return G.deck.pop() || null;
}

// ─── UNO Button ──────────────────────────────────────────────────────────────
document.getElementById('unoBtn').addEventListener('click', () => {
  G.unoSaid.human = true;
  setLog('📢 UNO! You declared UNO!');
  document.getElementById('unoBtn').classList.add('hidden');
  document.getElementById('humanUnoBadge').classList.remove('hidden');
});

// ─── Win / Lose ───────────────────────────────────────────────────────────────
function endGame(winner) {
  G.gameOver = true;
  const won = winner === 'human';

  document.getElementById('winEmoji').textContent = won ? '🏆' : '😞';
  document.getElementById('winTitle').textContent = won ? 'You Win!' : 'AI Wins!';
  document.getElementById('winSub').textContent   = won ? 'Amazing! You beat the AI!' : 'The AI emptied its hand first. Try again!';
  document.getElementById('statCards').textContent = G.cardsPlayed;
  document.getElementById('statDrawn').textContent  = G.cardsDrawn;

  setTimeout(() => {
    document.getElementById('winScreen').classList.remove('hidden');
  }, 500);
}

// ─── Log ──────────────────────────────────────────────────────────────────────
function setLog(msg) {
  document.getElementById('logText').textContent = msg;
}

// ─── Navigation ───────────────────────────────────────────────────────────────
document.getElementById('startBtn').addEventListener('click', () => {
  document.getElementById('startScreen').classList.remove('active');
  document.getElementById('startScreen').classList.add('hidden');
  document.getElementById('gameScreen').classList.remove('hidden');
  initGame();
});

document.getElementById('playAgainBtn').addEventListener('click', () => {
  document.getElementById('winScreen').classList.add('hidden');
  initGame();
});

document.getElementById('menuBtn').addEventListener('click', () => {
  document.getElementById('winScreen').classList.add('hidden');
  document.getElementById('gameScreen').classList.add('hidden');
  document.getElementById('startScreen').classList.remove('hidden');
  document.getElementById('startScreen').classList.add('active');
});
