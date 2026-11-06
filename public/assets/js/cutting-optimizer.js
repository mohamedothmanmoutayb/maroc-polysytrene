/**
 * Plan de découpe 3D optimisé (polystyrène).
 *
 * - Moteur : empilement 3D "guillotine" (chaque coupe traverse tout le volume,
 *   comme un fil chaud / une scie), avec rotation des pièces, trait de coupe,
 *   plusieurs stratégies de tri / de découpe testées, meilleur résultat conservé.
 * - Objectif : maximiser le volume utile par bloc => moins de blocs, moins de chutes,
 *   et des chutes regroupées en gros morceaux réutilisables.
 * - Vue : Three.js (orbite, éclaté, coupe verticale, survol des pièces).
 *
 * Dimensions en cm, volumes affichés en m³.
 * Dépendances (chargées par la page) : THREE, THREE.OrbitControls.
 */
(function (global) {
    'use strict';

    var EPS = 1e-6;
    var PALETTE = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#06b6d4'];
    var PERMS = [[0, 1, 2], [0, 2, 1], [1, 0, 2], [1, 2, 0], [2, 0, 1], [2, 1, 0]];
    var AXIS = ['X (largeur)', 'Y (hauteur)', 'Z (profondeur)'];

    /* ------------------------------------------------------------------ */
    /* Utilitaires                                                         */
    /* ------------------------------------------------------------------ */

    function vol(d) { return d[0] * d[1] * d[2]; }
    function m3(cm3) { return cm3 / 1e6; }
    function fmt(n, dec) {
        return Number(n).toLocaleString('fr-FR', { minimumFractionDigits: dec || 0, maximumFractionDigits: dec == null ? 2 : dec });
    }
    function r2(n) { return Math.round(n * 100) / 100; }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function rng(seed) {
        return function () {
            seed |= 0; seed = seed + 0x6D2B79F5 | 0;
            var t = Math.imul(seed ^ seed >>> 15, 1 | seed);
            t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }
    function tick() { return new Promise(function (r) { setTimeout(r, 0); }); }

    function orientations(d, rotate) {
        if (!rotate) return [d.slice()];
        var seen = {}, out = [];
        PERMS.forEach(function (p) {
            var o = [d[p[0]], d[p[1]], d[p[2]]], k = o.join('|');
            if (!seen[k]) { seen[k] = 1; out.push(o); }
        });
        return out;
    }

    /* ------------------------------------------------------------------ */
    /* Moteur d'empilement                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Remplit un bloc avec les pièces `inst` dans l'ordre `order`.
     * perm  : ordre des axes pour la découpe guillotine (quel axe garde le grand reste)
     * fit   : 0 = meilleur ajustement en volume, 1 = meilleur ajustement sur le plus petit côté
     */
    function packBlock(blockDims, inst, order, perm, fit, kerf, minDim) {
        var free = [{ p: [0, 0, 0], s: blockDims.slice() }];
        var dead = [];            // restes trop petits pour une pièce
        var placed = [];
        var failed = {};          // type déjà impossible à placer => inutile de réessayer
        var a = perm[0], b = perm[1], c = perm[2];

        for (var oi = 0; oi < order.length; oi++) {
            var it = inst[order[oi]];
            if (failed[it.t]) continue;

            var best = null;
            for (var fi = 0; fi < free.length; fi++) {
                var f = free[fi];
                for (var k = 0; k < it.orients.length; k++) {
                    var o = it.orients[k];
                    if (o[0] > f.s[0] + EPS || o[1] > f.s[1] + EPS || o[2] > f.s[2] + EPS) continue;
                    var score = fit === 0
                        ? f.s[0] * f.s[1] * f.s[2] - o[0] * o[1] * o[2]
                        : Math.min(f.s[0] - o[0], f.s[1] - o[1], f.s[2] - o[2]);
                    if (!best || score < best.score - EPS) best = { fi: fi, o: o, score: score };
                }
            }
            if (!best) { failed[it.t] = true; continue; }

            var f0 = free[best.fi], o0 = best.o;
            free.splice(best.fi, 1);

            // Espace consommé = pièce + trait de coupe (sans dépasser le volume libre).
            var occ = [
                Math.min(o0[0] + kerf, f0.s[0]),
                Math.min(o0[1] + kerf, f0.s[1]),
                Math.min(o0[2] + kerf, f0.s[2])
            ];

            var cuts = [];
            for (var ax = 0; ax < 3; ax++) {
                if (o0[ax] < f0.s[ax] - EPS) cuts.push({ axis: ax, at: r2(f0.p[ax] + o0[ax]) });
            }
            placed.push({
                t: it.t, x: f0.p[0], y: f0.p[1], z: f0.p[2], w: o0[0], h: o0[1], d: o0[2],
                box: { p: f0.p.slice(), s: f0.s.slice() }, cuts: cuts
            });

            // Découpe guillotine du reste en 3 volumes.
            var boxes = [];
            var sA = f0.s.slice(), pA = f0.p.slice();
            pA[a] += occ[a]; sA[a] = f0.s[a] - occ[a];
            boxes.push({ p: pA, s: sA });

            var sB = f0.s.slice(), pB = f0.p.slice();
            sB[a] = occ[a]; pB[b] += occ[b]; sB[b] = f0.s[b] - occ[b];
            boxes.push({ p: pB, s: sB });

            var sC = f0.s.slice(), pC = f0.p.slice();
            sC[a] = occ[a]; sC[b] = occ[b]; pC[c] += occ[c]; sC[c] = f0.s[c] - occ[c];
            boxes.push({ p: pC, s: sC });

            for (var bi = 0; bi < boxes.length; bi++) {
                var bx = boxes[bi];
                if (bx.s[0] <= 0.05 || bx.s[1] <= 0.05 || bx.s[2] <= 0.05) continue;
                if (bx.s[0] + EPS >= minDim && bx.s[1] + EPS >= minDim && bx.s[2] + EPS >= minDim) free.push(bx);
                else dead.push(bx);
            }
            if (!free.length) break;
        }

        var used = 0;
        placed.forEach(function (p) { used += p.w * p.h * p.d; });
        var remnants = free.concat(dead);
        var biggest = 0;
        remnants.forEach(function (r) { biggest = Math.max(biggest, vol(r.s)); });
        return { placed: placed, remnants: remnants, used: used, biggest: biggest };
    }

    function better(a, b) {
        if (!b) return true;
        if (a.used > b.used + 1) return true;
        if (a.used < b.used - 1) return false;
        if (a.placed.length !== b.placed.length) return a.placed.length > b.placed.length;
        if (a.remnants.length !== b.remnants.length) return a.remnants.length < b.remnants.length;
        return a.biggest > b.biggest;
    }

    function buildOrders(inst, random, extraRandom) {
        var idx = inst.map(function (_, i) { return i; });
        function sorted(keyFn) {
            return idx.slice().sort(function (x, y) { return keyFn(inst[y]) - keyFn(inst[x]); });
        }
        var sets = [
            sorted(function (i) { return vol(i.dims); }),
            sorted(function (i) { return Math.max.apply(null, i.dims); }),
            sorted(function (i) { return Math.min.apply(null, i.dims); }),
            sorted(function (i) { var d = i.dims.slice().sort(function (p, q) { return q - p; }); return d[0] * d[1]; })
        ];
        for (var r = 0; r < extraRandom; r++) {
            var base = sets[r % sets.length].slice();
            // petit bruit : on mélange par blocs pour explorer d'autres ordres
            for (var i = base.length - 1; i > 0; i--) {
                if (random() < 0.35) {
                    var j = Math.max(0, i - 1 - Math.floor(random() * 4));
                    var tmp = base[i]; base[i] = base[j]; base[j] = tmp;
                }
            }
            sets.push(base);
        }
        return sets;
    }

    function fillBlock(blockDims, inst, opts) {
        var n = inst.length;
        var randomCount = n <= 40 ? 24 : n <= 150 ? 8 : n <= 500 ? 2 : 0;
        var random = rng(1234 + n);
        var orders = buildOrders(inst, random, randomCount);

        var combos = [];
        PERMS.forEach(function (perm) {
            [0, 1].forEach(function (fit) {
                orders.forEach(function (order) { combos.push({ perm: perm, fit: fit, order: order }); });
            });
        });
        var limit = n <= 60 ? 400 : n <= 200 ? 90 : n <= 600 ? 36 : 14;
        if (combos.length > limit) {
            var stride = combos.length / limit, kept = [];
            for (var i = 0; i < limit; i++) kept.push(combos[Math.floor(i * stride)]);
            combos = kept;
        }

        var best = null;
        for (var k = 0; k < combos.length; k++) {
            var cb = combos[k];
            var res = packBlock(blockDims, inst, cb.order, cb.perm, cb.fit, opts.kerf, opts.minDim);
            if (better(res, best)) { best = res; best.perm = cb.perm; }
        }
        return best;
    }

    /**
     * Calcule le plan complet : blocs sources disponibles d'abord, puis blocs
     * supplémentaires si nécessaire.
     */
    async function solve(sources, pieces, opts, onProgress) {
        var types = [];
        var inst = [];
        pieces.forEach(function (pc, t) {
            var d = [pc.w, pc.h, pc.d];
            var orients = orientations(d, opts.rotate);
            types.push({ name: pc.name, code: pc.code, dims: d, qty: pc.qty, color: PALETTE[t % PALETTE.length] });
            for (var q = 0; q < pc.qty; q++) inst.push({ t: t, dims: d, orients: orients });
        });

        var warnings = [];
        var blockList = [];
        sources.forEach(function (s, si) {
            for (var q = 0; q < s.qty; q++) blockList.push({ src: si, dims: [s.w, s.h, s.d], extra: false });
        });

        // Pièces qui ne rentrent dans aucun bloc source.
        var fits = function (it, dims) {
            return it.orients.some(function (o) { return o[0] <= dims[0] + EPS && o[1] <= dims[1] + EPS && o[2] <= dims[2] + EPS; });
        };
        var oversize = {};
        var remaining = inst.filter(function (it) {
            var ok = sources.some(function (s) { return fits(it, [s.w, s.h, s.d]); });
            if (!ok) oversize[it.t] = (oversize[it.t] || 0) + 1;
            return ok;
        });
        Object.keys(oversize).forEach(function (t) {
            warnings.push({ k: 'w_oversize', p: { name: types[t].name, dims: types[t].dims.join(' × '), n: oversize[t] } });
        });

        var minDim = Infinity;
        remaining.forEach(function (it) { minDim = Math.min(minDim, Math.min.apply(null, it.dims)); });
        if (!isFinite(minDim)) minDim = 1;
        var runOpts = { kerf: opts.kerf, minDim: minDim };

        var blocks = [];
        var totalToPlace = remaining.length;
        var cursor = 0;

        while (remaining.length && blocks.length < 500) {
            var bl;
            if (cursor < blockList.length) {
                bl = blockList[cursor++];
            } else {
                // Bloc supplémentaire : le plus petit type de bloc qui accepte la plus grosse pièce restante.
                var cand = sources.map(function (s, si) { return { si: si, d: [s.w, s.h, s.d] }; })
                    .filter(function (c) { return remaining.some(function (it) { return fits(it, c.d); }); })
                    .sort(function (x, y) { return vol(x.d) - vol(y.d); })[0];
                if (!cand) break;
                bl = { src: cand.si, dims: cand.d, extra: true };
            }
            if (!(vol(bl.dims) > 0)) { continue; }

            var res = fillBlock(bl.dims, remaining, runOpts);
            if (!res.placed.length) {
                if (bl.extra) break;
                blocks.push({ src: bl.src, dims: bl.dims, extra: false, placed: [], remnants: [{ p: [0, 0, 0], s: bl.dims.slice() }], used: 0 });
                continue;
            }

            // Retire les pièces placées (par type, ordre quelconque).
            var take = {};
            res.placed.forEach(function (p) { take[p.t] = (take[p.t] || 0) + 1; });
            remaining = remaining.filter(function (it) {
                if (take[it.t] > 0) { take[it.t]--; return false; }
                return true;
            });

            blocks.push({ src: bl.src, dims: bl.dims, extra: bl.extra, placed: res.placed, remnants: res.remnants, used: res.used });
            if (onProgress) onProgress(totalToPlace - remaining.length, totalToPlace, blocks.length);
            await tick();
        }

        if (remaining.length) warnings.push({ k: 'w_unplaced', p: { n: remaining.length } });

        // Totaux
        var usedBlocks = blocks.filter(function (b) { return b.placed.length; });
        var blockVol = 0, pieceVol = 0, remnantVol = 0, recoverVol = 0, pieceCount = 0;
        var minRecover = opts.minRecover;
        usedBlocks.forEach(function (b) {
            blockVol += vol(b.dims);
            pieceVol += b.used;
            pieceCount += b.placed.length;
            b.remnants.forEach(function (r) {
                var v = vol(r.s);
                remnantVol += v;
                r.recoverable = Math.min.apply(null, r.s) >= minRecover;
                if (r.recoverable) recoverVol += v;
            });
        });
        var totalPieceVolNeeded = 0;
        types.forEach(function (t) { totalPieceVolNeeded += vol(t.dims) * t.qty; });
        var avgBlock = usedBlocks.length ? blockVol / usedBlocks.length : 0;

        // Capacité d'un bloc pour chaque type de pièce (explique "pourquoi N blocs").
        var capacity = [];
        var usedSrc = {};
        usedBlocks.forEach(function (b) { usedSrc[b.src] = 1; });
        Object.keys(usedSrc).forEach(function (si) {
            var sd = [sources[si].w, sources[si].h, sources[si].d];
            types.forEach(function (ty, ti) {
                var ratio = vol(sd) / vol(ty.dims);
                if (ratio > 300) { capacity.push({ src: +si, type: ti, cap: Math.floor(ratio), ratio: ratio, many: true }); return; }
                var count = Math.min(Math.floor(ratio) + 2, 320), list = [];
                var orients = orientations(ty.dims, opts.rotate);
                for (var q = 0; q < count; q++) list.push({ t: ti, dims: ty.dims, orients: orients });
                var r1 = fillBlock(sd, list, { kerf: opts.kerf, minDim: Math.min.apply(null, ty.dims) });
                capacity.push({ src: +si, type: ti, cap: r1.placed.length, ratio: ratio });
            });
        });

        return {
            types: types, blocks: usedBlocks, warnings: warnings, capacity: capacity,
            totals: {
                blocksUsed: usedBlocks.length,
                blocksAvailable: blockList.length,
                pieceCount: pieceCount, pieceTarget: inst.length,
                blockVol: blockVol, pieceVol: pieceVol,
                wasteVol: blockVol - pieceVol,
                remnantVol: remnantVol, recoverVol: recoverVol,
                kerfVol: Math.max(0, blockVol - pieceVol - remnantVol),
                efficiency: blockVol ? pieceVol / blockVol * 100 : 0,
                theoreticalMin: avgBlock ? Math.ceil(totalPieceVolNeeded / avgBlock - 1e-9) : 0
            }
        };
    }

    /* ------------------------------------------------------------------ */
    /* Textes (français / arabe)                                           */
    /* ------------------------------------------------------------------ */

    var TXT = {
        fr: {
            u_cm: 'cm', u_m3: 'm³',
            kerf_d: 'Épaisseur de matière détruite par le fil chaud ou la lame à chaque coupe. 0 = on l\'ignore. Plus elle est grande, moins de pièces tiennent dans un bloc (ex. 2 mm pour une lame).',
            minrec_d: 'Un morceau restant est compté comme « chute récupérable » (zone bleue) si son plus petit côté mesure au moins cette valeur. Sinon il est compté comme perte (zone rouge).',
            rot_d: 'Coché : les pièces peuvent être couchées ou debout pour mieux remplir le bloc. Décoché : chaque pièce garde l\'orientation de sa fiche produit.',
            run_d: 'Relance le calcul avec ces réglages. Le calcul se relance aussi tout seul quand vous changez le formulaire.', help_btn: 'Aide : comprendre chaque élément', explode: 'Éclaté', clip: 'Coupe', chutes: 'Chutes', wire: 'Filaire',
            spin: 'Rotation auto', reset: 'Recentrer', busy: 'Calcul du meilleur plan…',
            busy_n: function (p) { return 'Calcul… ' + p.done + ' / ' + p.all + ' pièces (' + p.nb + ' bloc' + (p.nb > 1 ? 's' : '') + ')'; },
            blocks_used: 'Blocs utilisés', th_num: '#', th_block: 'Bloc', th_pieces: 'Pièces', th_eff: 'Rendement', th_chute: 'Chute',
            params: 'Paramètres', kerf: 'Trait de coupe (mm)', minrec: 'Chute récupérable ≥ (cm)', rot: 'Autoriser la rotation des pièces', run: 'Optimiser',
            src_title: 'Blocs sources (cm)', pc_title: 'Pièces à découper (cm)',
            th_name: 'Nom', th_len: 'Longueur', th_h: 'Hauteur', th_depth: 'Profondeur', th_qty: 'Quantité',
            detail_title: 'Détail de découpe du bloc affiché',
            kpi_eff: 'Rendement matière', kpi_blocks: 'Blocs nécessaires', avail: 'dispo.', kpi_min: function (p) { return 'Minimum théorique : ' + p.n; },
            kpi_pieces: 'Pièces placées', kpi_vol: 'Volume utile', kpi_on: function (p) { return 'sur ' + p.v + ' m³ de blocs'; },
            kpi_rec: 'Chutes récupérables', kpi_rec_sub: function (p) { return p.pct + ' % — réutilisables (type 5)'; },
            kpi_loss: 'Perte réelle', kpi_loss_sub: function (p) { return 'dont trait de coupe ≈ ' + p.v + ' m³'; },
            legend_rec: 'Chute récupérable (bleu)', legend_loss: 'Perte (rouge)',
            block_opt: function (p) { return 'Bloc ' + p.i + ' / ' + p.n + (p.extra ? ' (supplémentaire)' : ''); }, badge_extra: 'en plus',
            tip_rec: 'Chute récupérable', tip_loss: 'Chute (perte)',
            d_contains: 'Ce que contient ce bloc', d_piece_n: function (p) { return p.n + ' pièce' + (p.n > 1 ? 's' : ''); },
            d_cut_with: 'À découper avec', d_rec_title: 'Chutes à récupérer (les plus grandes)', d_rec_none: 'Aucune chute assez grande',
            d_steps: 'Ordre des coupes', d_zone: 'Zone de travail', d_start: 'départ à',
            d_cut: function (p) { return '✂ Coupe ' + p.k + ' : à <b>' + p.at + '</b> du bord du bloc, dans le sens de la <b>' + p.side + '</b>'; },
            d_nocut: 'Aucune coupe : la pièce remplit exactement cette zone.', d_more: function (p) { return '… et ' + p.n + ' autres pièces'; },
            d_read: 'Comment lire : on mesure toujours depuis un coin du bloc. <b>Longueur</b> = de gauche à droite, <b>Hauteur</b> = de bas en haut, <b>Profondeur</b> = de l\'avant vers l\'arrière. Chaque coupe traverse toute la zone de travail indiquée.',
            side0: 'Longueur', side1: 'Hauteur', side2: 'Profondeur', unit_len: '(Longueur)', unit_h: '(Hauteur)', unit_d: '(Profondeur)',
            w_missing: function (p) { return 'Dimensions manquantes pour : ' + p.names + ' — renseignez-les dans la fiche produit.'; },
            w_nosrc: 'Aucun bloc source avec des dimensions valides — renseignez Longueur × Hauteur × Profondeur dans la fiche produit.',
            w_nopc: 'Aucune pièce avec des dimensions valides.', w_toomany: function (p) { return 'Trop de pièces (' + p.n + ') — limite 20 000.'; },
            w_oversize: function (p) { return '« ' + p.name + ' » (' + p.dims + ' cm) ne rentre dans aucun bloc source (non planifié : ' + p.n + ').'; },
            w_unplaced: function (p) { return 'Impossible de placer ' + p.n + ' pièce(s).'; }, w_err: function (p) { return 'Erreur de calcul : ' + p.m; },
            hint: 'Choisissez le bloc source et les produits à produire : le plan de découpe 3D se calcule ici automatiquement.',
            flow_title: 'Ce que donne la découpe', flow_blocks: function (p) { return p.n + ' bloc' + (p.n > 1 ? 's' : ''); },
            flow_scrap_rec: 'Chutes à garder', flow_scrap_loss: 'Perte',
            this_gives: 'Ce que donne ce bloc',
            step_title: 'Étapes de découpe', step_overview: 'Vue d\'ensemble', step_n: function (p) { return 'Étape ' + p.k + ' / ' + p.n; },
            step_hint: 'Appuyez sur ▶ pour voir les coupes une par une, dans l\'ordre.',
            step_play: 'Lecture', step_pause: 'Pause', step_prev: 'Étape précédente', step_next: 'Étape suivante', step_first: 'Vue d\'ensemble',
            cut_k: function (p) { return 'Coupe ' + p.k; }, from_edge: function (p) { return 'à ' + p.at + ' du bord'; },
            view_face: 'Vue de face', view_top: 'Vue de dessus', step_done: 'Déjà coupé', step_now: 'À couper', step_zone: 'Zone de travail',
            axes_t: 'Sens de mesure',
            why_title: function (p) { return 'Pourquoi ' + p.n + ' bloc' + (p.n > 1 ? 's' : '') + ' ?'; },
            y_need: function (p) { return 'À produire : <b>' + p.pcs + ' pièce' + (p.pcs > 1 ? 's' : '') + '</b> (volume utile ' + p.v + ' m³).'; },
            y_cap: function (p) { return 'Un bloc « <b>' + p.sname + '</b> » (' + p.sdims + ' cm, ' + p.sv + ' m³) peut contenir au maximum <b>' + p.cap + ' × « ' + p.pname + ' »</b> (' + p.pdims + ' cm, ' + p.pv + ' m³).'; },
            y_shape: function (p) { return 'Le volume seul permettrait ' + p.ratio + ' pièce(s), mais <b>la forme</b> ne le permet pas : la section de la pièce ne se répète pas dans celle du bloc, et ce qui reste est trop étroit pour une autre pièce.'; },
            y_vol: function (p) { return 'Le bloc ne fait que <b>' + p.ratio + ' fois</b> le volume d\'une pièce : il ne peut donc en contenir que <b>' + p.cap + '</b> (pour en mettre ' + (p.cap + 1) + ', il faudrait au moins ' + p.next + ' m³ de matière).'; },
            y_many: function (p) { return 'Un bloc « <b>' + p.sname + '</b> » peut contenir un grand nombre de « ' + p.pname + ' » (plus de ' + p.cap + ').'; },
            y_result: function (p) { return 'Résultat : <b>' + p.n + ' bloc(s)</b> pour ' + p.pcs + ' pièce' + (p.pcs > 1 ? 's' : '') + '. Le « minimum théorique » (' + p.min + ') ne compte que le volume ; la forme des pièces peut obliger à utiliser plus de blocs.'; },
            y_eff: function (p) { return 'Rendement ' + p.eff + ' % : ' + p.rec + ' m³ (' + p.pct + ' %) restent en chutes récupérables — ce sont les zones bleues dans la vue 3D, réutilisables pour un ordre de type 5.'; },
            h_title: 'Comprendre chaque élément',
            h: [
                ['Rendement matière', 'Part du volume des blocs qui devient des pièces utiles. 100 % = aucune chute. Vert ≥ 85 %, orange ≥ 70 %, rouge en dessous.'],
                ['Blocs nécessaires', 'Nombre de blocs que le meilleur plan utilise, comparé aux blocs que vous avez indiqués. Rouge si le plan demande plus de blocs que disponibles ("en plus").'],
                ['Minimum théorique', 'Nombre de blocs si on ne regardait que le volume (volume des pièces ÷ volume d\'un bloc). Le réel peut être plus grand car les pièces ont une forme.'],
                ['Pièces placées', 'Pièces que le plan a réussi à placer sur le nombre demandé. Rouge si certaines n\'ont pas de place.'],
                ['Volume utile', 'Volume total des pièces produites, comparé au volume des blocs consommés.'],
                ['Chutes récupérables', 'Morceaux restants assez gros (≥ le seuil réglé dans les paramètres) pour être réutilisés, par exemple dans un ordre de production de type 5. Ils sont dessinés en bleu en 3D.'],
                ['Perte réelle', 'Morceaux trop petits pour servir (dessinés en rouge) plus la matière perdue par le trait de coupe.'],
                ['Trait de coupe (mm)', 'Épaisseur de matière détruite par la lame / le fil. 0 = ignoré. Plus il est grand, moins de pièces tiennent dans un bloc.'],
                ['Chute récupérable ≥ (cm)', 'Un reste est "récupérable" si son plus petit côté est au moins égal à cette valeur.'],
                ['Autoriser la rotation', 'Permet de tourner les pièces dans le bloc (couchées, debout) pour mieux remplir. Décochez si la pièce doit garder son sens (ex. fibre, face lisse).'],
                ['Optimiser', 'Relance le calcul. Il se relance aussi tout seul quand vous changez le formulaire.'],
                ['Tableaux des blocs et pièces', 'Dimensions en cm prises de la fiche produit. Longueur = gauche-droite, Hauteur = bas-haut, Profondeur = avant-arrière. Ces tableaux sont en lecture seule : pour changer une dimension, modifiez la fiche du produit.'],
                ['Vue 3D', 'Souris : glisser = tourner, molette = zoom, clic droit = déplacer. Survolez une pièce pour voir son nom et ses dimensions. ‹ › ou la liste = changer de bloc.'],
                ['Éclaté / Coupe', 'Éclaté écarte les pièces pour les voir séparément. Coupe masque ce qui est au-dessus d\'une hauteur pour voir l\'intérieur.'],
                ['Chutes / Filaire / Rotation auto', 'Afficher ou cacher les chutes, rendre les pièces transparentes, faire tourner la vue automatiquement. Recentrer remet la caméra par défaut.'],
                ['Couleurs', 'Chaque produit a sa couleur (voir la légende sous la vue). Bleu transparent = chute récupérable. Rouge transparent = perte.'],
                ['Blocs utilisés', 'Une ligne par bloc : nombre de pièces, rendement et chute en m³. Cliquez une ligne pour l\'afficher en 3D.'],
                ['Ce que donne la découpe', 'Résumé en images : les blocs utilisés → les pièces obtenues (chaque petite boîte respecte les proportions réelles) + les chutes à garder (bleu) et la perte (rouge).'],
                ['Étapes de découpe (▶)', 'Lecture pas à pas : ▶ montre chaque coupe dans l\'ordre. La pièce à couper est en couleur vive avec un contour jaune, les pièces déjà coupées sont atténuées, la zone de travail est en jaune. Les plans de coupe sont colorés selon le sens.'],
                ['Couleurs des sens', 'Orange = Longueur (gauche → droite), Vert = Hauteur (bas → haut), Violet = Profondeur (avant → arrière). Les barres de couleur sur les arêtes du bloc et les plans de coupe suivent les mêmes couleurs.'],
                ['Vue de face / de dessus', 'Deux petits plans du bloc : la zone jaune est la zone de travail, la pièce colorée est celle à obtenir, les traits pointillés sont les coupes à faire. Cliquez une étape de la liste pour la voir.'],
                ['Détail de découpe', '« Ce que donne ce bloc » liste les pièces obtenues et leur répartition. « Chutes à récupérer » liste les plus gros restes. « Étapes de découpe » donne, pièce par pièce, où couper (en cm depuis le bord du bloc).']
            ]
        },
        ar: {
            u_cm: 'سم', u_m3: 'م³',
            kerf_d: 'سُمك المادة التي يُتلفها السلك الساخن أو السكين عند كل عملية قصّ. 0 = يتم تجاهله. كلما زاد قلّ عدد القطع التي تتسع في الكتلة (مثال: 2 مم للسكين).',
            minrec_d: 'تُحتسب القطعة المتبقية «قصاصة قابلة للاسترجاع» (المنطقة الزرقاء) إذا كان أصغر أبعادها يساوي هذه القيمة على الأقل. وإلا تُحتسب خسارة (المنطقة الحمراء).',
            rot_d: 'عند التحديد: يمكن وضع القطع مستلقية أو واقفة لملء الكتلة بشكل أفضل. عند عدم التحديد: تحتفظ كل قطعة باتجاهها كما في بطاقة المنتج.',
            run_d: 'يعيد الحساب بهذه الإعدادات. ويُعاد الحساب تلقائيًا أيضًا عند تغيير النموذج.', help_btn: 'مساعدة: فهم كل عنصر', explode: 'تفكيك', clip: 'مقطع', chutes: 'القصاصات', wire: 'هيكل شفاف',
            spin: 'دوران تلقائي', reset: 'إعادة التمركز', busy: 'جارٍ حساب أفضل خطة…',
            busy_n: function (p) { return 'جارٍ الحساب… ' + p.done + ' / ' + p.all + ' قطعة (' + p.nb + ' كتلة)'; },
            blocks_used: 'الكتل المستعملة', th_num: '#', th_block: 'الكتلة', th_pieces: 'القطع', th_eff: 'المردودية', th_chute: 'القصاصات',
            params: 'الإعدادات', kerf: 'سُمك القصّ (مم)', minrec: 'قصاصة قابلة للاسترجاع ≥ (سم)', rot: 'السماح بتدوير القطع', run: 'تحسين',
            src_title: 'الكتل المصدر (سم)', pc_title: 'القطع المطلوب قصّها (سم)',
            th_name: 'الاسم', th_len: 'الطول', th_h: 'الارتفاع', th_depth: 'العمق', th_qty: 'الكمية',
            detail_title: 'تفاصيل قصّ الكتلة المعروضة',
            kpi_eff: 'مردودية المادة', kpi_blocks: 'الكتل اللازمة', avail: 'متاحة', kpi_min: function (p) { return 'الحد الأدنى النظري : ' + p.n; },
            kpi_pieces: 'القطع الموضوعة', kpi_vol: 'الحجم المفيد', kpi_on: function (p) { return 'من أصل ' + p.v + ' م³ من الكتل'; },
            kpi_rec: 'قصاصات قابلة للاسترجاع', kpi_rec_sub: function (p) { return p.pct + ' % — يمكن إعادة استعمالها (النوع 5)'; },
            kpi_loss: 'الخسارة الفعلية', kpi_loss_sub: function (p) { return 'منها سُمك القصّ ≈ ' + p.v + ' م³'; },
            legend_rec: 'قصاصة قابلة للاسترجاع (أزرق)', legend_loss: 'خسارة (أحمر)',
            block_opt: function (p) { return 'الكتلة ' + p.i + ' / ' + p.n + (p.extra ? ' (إضافية)' : ''); }, badge_extra: 'إضافية',
            tip_rec: 'قصاصة قابلة للاسترجاع', tip_loss: 'قصاصة (خسارة)',
            d_contains: 'ما تحتويه هذه الكتلة', d_piece_n: function (p) { return p.n + ' ' + (p.n > 1 && p.n < 11 ? 'قطع' : 'قطعة'); },
            d_cut_with: 'تُقصّ بالأبعاد', d_rec_title: 'القصاصات الواجب استرجاعها (الأكبر)', d_rec_none: 'لا توجد قصاصة كبيرة بما يكفي',
            d_steps: 'ترتيب القصّ', d_zone: 'منطقة العمل', d_start: 'تبدأ عند',
            d_cut: function (p) { return '✂ عملية القصّ ' + p.k + ' : على بُعد <b>' + p.at + '</b> من حافة الكتلة، في اتجاه <b>' + p.side + '</b>'; },
            d_nocut: 'لا يوجد قصّ: القطعة تملأ هذه المنطقة تمامًا.', d_more: function (p) { return '… و ' + p.n + ' قطعة أخرى'; },
            d_read: 'كيف تقرأ: نقيس دائمًا انطلاقًا من زاوية الكتلة. <b>الطول</b> = من اليسار إلى اليمين، <b>الارتفاع</b> = من الأسفل إلى الأعلى، <b>العمق</b> = من الأمام إلى الخلف. كل عملية قصّ تخترق منطقة العمل المحددة كاملةً.',
            side0: 'الطول', side1: 'الارتفاع', side2: 'العمق', unit_len: '(الطول)', unit_h: '(الارتفاع)', unit_d: '(العمق)',
            w_missing: function (p) { return 'أبعاد ناقصة للمنتج: ' + p.names + ' — أدخلها في بطاقة المنتج.'; },
            w_nosrc: 'لا توجد كتلة مصدر بأبعاد صحيحة — أدخل الطول × الارتفاع × العمق في بطاقة المنتج.',
            w_nopc: 'لا توجد قطعة بأبعاد صحيحة.', w_toomany: function (p) { return 'عدد القطع كبير جدًا (' + p.n + ') — الحد الأقصى 20 000.'; },
            w_oversize: function (p) { return '« ' + p.name + ' » (' + p.dims + ' سم) لا تدخل في أي كتلة مصدر: تم تجاهل ' + p.n + ' قطعة.'; },
            w_unplaced: function (p) { return 'تعذّر وضع ' + p.n + ' قطعة.'; }, w_err: function (p) { return 'خطأ في الحساب: ' + p.m; },
            hint: 'اختر الكتلة المصدر والمنتجات المطلوب إنتاجها: تُحسب خطة القصّ ثلاثية الأبعاد هنا تلقائيًا.',
            flow_title: 'ما تعطيه عملية القصّ', flow_blocks: function (p) { return p.n + ' ' + (p.n > 2 && p.n < 11 ? 'كتل' : 'كتلة'); },
            flow_scrap_rec: 'قصاصات للاحتفاظ بها', flow_scrap_loss: 'خسارة',
            this_gives: 'ما تعطيه هذه الكتلة',
            step_title: 'مراحل القصّ', step_overview: 'نظرة عامة', step_n: function (p) { return 'المرحلة ' + p.k + ' / ' + p.n; },
            step_hint: 'اضغط ▶ لرؤية عمليات القصّ واحدة تلو الأخرى بالترتيب.',
            step_play: 'تشغيل', step_pause: 'إيقاف مؤقت', step_prev: 'المرحلة السابقة', step_next: 'المرحلة التالية', step_first: 'نظرة عامة',
            cut_k: function (p) { return 'القصّ ' + p.k; }, from_edge: function (p) { return 'على بُعد ' + p.at + ' من الحافة'; },
            view_face: 'منظر أمامي', view_top: 'منظر علوي', step_done: 'تم قصّه', step_now: 'المطلوب قصّه', step_zone: 'منطقة العمل',
            axes_t: 'اتجاه القياس',
            why_title: function (p) { return 'لماذا ' + p.n + ' ' + (p.n > 2 && p.n < 11 ? 'كتل' : 'كتلة') + ' ؟'; },
            y_need: function (p) { return 'المطلوب إنتاجه: <b>' + p.pcs + ' قطعة</b> (الحجم المفيد ' + p.v + ' م³).'; },
            y_cap: function (p) { return 'الكتلة « <b>' + p.sname + '</b> » (' + p.sdims + ' سم، ' + p.sv + ' م³) تتسع على الأكثر لـ <b>' + p.cap + ' × « ' + p.pname + ' »</b> (' + p.pdims + ' سم، ' + p.pv + ' م³).'; },
            y_shape: function (p) { return 'الحجم وحده كان سيسمح بـ ' + p.ratio + ' قطعة، لكن <b>الشكل</b> لا يسمح: مقطع القطعة لا يتكرر داخل مقطع الكتلة، والجزء المتبقي أضيق من أن يتّسع لقطعة أخرى.'; },
            y_vol: function (p) { return 'حجم الكتلة لا يساوي سوى <b>' + p.ratio + ' مرة</b> حجم القطعة: لذلك لا تتسع إلا لـ <b>' + p.cap + '</b> (لوضع ' + (p.cap + 1) + ' يلزم ما لا يقل عن ' + p.next + ' م³ من المادة).'; },
            y_many: function (p) { return 'الكتلة « <b>' + p.sname + '</b> » تتسع لعدد كبير من « ' + p.pname + ' » (أكثر من ' + p.cap + ').'; },
            y_result: function (p) { return 'النتيجة: <b>' + p.n + ' كتلة</b> لإنتاج ' + p.pcs + ' قطعة. «الحد الأدنى النظري» (' + p.min + ') يعتمد على الحجم فقط؛ أما شكل القطع فقد يفرض استعمال كتل أكثر.'; },
            y_eff: function (p) { return 'المردودية ' + p.eff + ' % : يبقى ' + p.rec + ' م³ (' + p.pct + ' %) على شكل قصاصات قابلة للاسترجاع — وهي المناطق الزرقاء في العرض ثلاثي الأبعاد، ويمكن استعمالها في أمر إنتاج من النوع 5.'; },
            h_title: 'فهم كل عنصر',
            h: [
                ['مردودية المادة', 'نسبة حجم الكتل التي تتحول إلى قطع مفيدة. 100 % = لا توجد قصاصات. أخضر ≥ 85 %، برتقالي ≥ 70 %، وأحمر أقل من ذلك.'],
                ['الكتل اللازمة', 'عدد الكتل التي تستعملها أفضل خطة مقارنةً بالكتل التي أدخلتها. يظهر بالأحمر إذا احتاجت الخطة كتلًا أكثر من المتاح ("إضافية").'],
                ['الحد الأدنى النظري', 'عدد الكتل لو نظرنا إلى الحجم فقط (حجم القطع ÷ حجم الكتلة). قد يكون العدد الفعلي أكبر لأن للقطع شكلًا.'],
                ['القطع الموضوعة', 'القطع التي نجحت الخطة في وضعها من العدد المطلوب. أحمر إذا لم يوجد مكان لبعضها.'],
                ['الحجم المفيد', 'الحجم الإجمالي للقطع المنتَجة مقارنةً بحجم الكتل المستهلكة.'],
                ['قصاصات قابلة للاسترجاع', 'البقايا الكبيرة بما يكفي (≥ العتبة المحددة في الإعدادات) لإعادة استعمالها، مثلًا في أمر إنتاج من النوع 5. تظهر بالأزرق في العرض ثلاثي الأبعاد.'],
                ['الخسارة الفعلية', 'البقايا الصغيرة جدًا التي لا تصلح للاستعمال (بالأحمر) مضافًا إليها المادة الضائعة بسبب سُمك القطع.'],
                ['سُمك القصّ (مم)', 'سُمك المادة التي يُتلفها السكين / السلك. 0 = يتم تجاهله. كلما زاد قلّ عدد القطع التي تتسع في الكتلة.'],
                ['قصاصة قابلة للاسترجاع ≥ (سم)', 'تُعتبر القصاصة "قابلة للاسترجاع" إذا كان أصغر أبعادها يساوي هذه القيمة على الأقل.'],
                ['السماح بالتدوير', 'يسمح بتدوير القطع داخل الكتلة (مستلقية أو واقفة) لملء أفضل. ألغِ التحديد إذا كان يجب أن تحافظ القطعة على اتجاهها.'],
                ['تحسين', 'يعيد الحساب. ويُعاد تلقائيًا أيضًا عند تغيير النموذج.'],
                ['جدولا الكتل والقطع', 'الأبعاد بالسنتيمتر مأخوذة من بطاقة المنتج. الطول = يسار-يمين، الارتفاع = أسفل-أعلى، العمق = أمام-خلف. هذان الجدولان للقراءة فقط: لتغيير بُعد ما، عدّل بطاقة المنتج.'],
                ['العرض ثلاثي الأبعاد', 'الفأرة: السحب = تدوير، العجلة = تكبير، النقر الأيمن = تحريك. مرّر المؤشر فوق قطعة لرؤية اسمها وأبعادها. ‹ › أو القائمة = تغيير الكتلة.'],
                ['تفكيك / مقطع', 'التفكيك يُبعد القطع عن بعضها لرؤيتها منفصلة. المقطع يخفي ما فوق ارتفاع معيّن لرؤية الداخل.'],
                ['القصاصات / هيكل شفاف / دوران تلقائي', 'إظهار القصاصات أو إخفاؤها، جعل القطع شفافة، تدوير العرض تلقائيًا. "إعادة التمركز" تعيد الكاميرا إلى وضعها الافتراضي.'],
                ['الألوان', 'لكل منتج لونه (انظر المفتاح أسفل العرض). الأزرق الشفاف = قصاصة قابلة للاسترجاع. الأحمر الشفاف = خسارة.'],
                ['الكتل المستعملة', 'سطر لكل كتلة: عدد القطع والمردودية والقصاصات بالمتر المكعب. انقر على سطر لعرضه بالأبعاد الثلاثة.'],
                ['ما تعطيه عملية القصّ', 'ملخص بالصور: الكتل المستعملة ← القطع الناتجة (كل صندوق صغير يحترم النسب الحقيقية) + القصاصات التي يجب الاحتفاظ بها (أزرق) والخسارة (أحمر).'],
                ['مراحل القصّ (▶)', 'عرض خطوة بخطوة: ▶ يعرض كل عملية قصّ بالترتيب. القطعة المطلوب قصّها بلون ساطع وإطار أصفر، والقطع المقصوصة سابقًا باهتة، ومنطقة العمل بالأصفر. مستويات القصّ ملوّنة حسب الاتجاه.'],
                ['ألوان الاتجاهات', 'برتقالي = الطول (يسار ← يمين)، أخضر = الارتفاع (أسفل ← أعلى)، بنفسجي = العمق (أمام ← خلف). أشرطة الألوان على حواف الكتلة ومستويات القصّ تتبع نفس الألوان.'],
                ['منظر أمامي / علوي', 'مخططان صغيران للكتلة: المنطقة الصفراء هي منطقة العمل، والقطعة الملوّنة هي المطلوب الحصول عليها، والخطوط المتقطعة هي عمليات القصّ. انقر على مرحلة من القائمة لعرضها.'],
                ['تفاصيل القصّ', '«ما تعطيه هذه الكتلة» يعرض القطع الناتجة وتوزيعها. «القصاصات الواجب استرجاعها» يعرض أكبر البقايا. «مراحل القصّ» يحدد لكل قطعة أين يجب القصّ (بالسنتيمتر من حافة الكتلة).']
            ]
        }
    };


    /* Vocabulaire selon le type d'ordre : type2 = bloc -> sous-blocs, type3 = sous-blocs -> produits finaux,
       type4 = produits sources -> produits cibles. Remplacement en une passe (plus long d'abord). */
    function frPieces(one, many, oneDe, art, lo, placed, toDo) {
        return [['Pièces à découper', toDo], ["d'une pièce", "d'un " + one], ['Aucune pièce', 'Aucun ' + one], ['une pièce', 'un ' + one],
            ['cette pièce', 'ce ' + one], ['la pièce', 'le ' + one], ['La pièce', 'Le ' + one], ['Pièces placées', placed],
            ['pièces', many], ['pièce', one], ['Pièces', many.charAt(0).toUpperCase() + many.slice(1)], ['Pièce', one.charAt(0).toUpperCase() + one.slice(1)]];
    }
    var VOCAB = {
        fr: {
            type2: frPieces('sous-bloc', 'sous-blocs', 0, 0, 0, 'Sous-blocs placés', 'Sous-blocs à découper'),
            type3: [['Blocs', 'Sous-blocs'], ['blocs', 'sous-blocs'], ['Bloc', 'Sous-bloc'], ['bloc', 'sous-bloc']]
                .concat(frPieces('produit final', 'produits finaux', 0, 0, 0, 'Produits finaux placés', 'Produits finaux à produire')),
            type4: [['Blocs sources', 'Produits sources'], ['blocs sources', 'produits sources'], ['Bloc source', 'Produit source'], ['bloc source', 'produit source'],
                ['Blocs', 'Produits sources'], ['blocs', 'produits sources'], ['Bloc', 'Produit source'], ['bloc', 'produit source']]
                .concat(frPieces('produit cible', 'produits cibles', 0, 0, 0, 'Produits cibles placés', 'Produits cibles à produire'))
        },
        ar: {
            type2: [['القطعة', 'الكتلة الفرعية'], ['قطعة', 'كتلة فرعية'], ['القطع', 'الكتل الفرعية'], ['قطع', 'كتل فرعية']],
            type3: [['الكتلة', 'الكتلة الفرعية'], ['الكتل', 'الكتل الفرعية'], ['كتلة', 'كتلة فرعية'], ['كتل', 'كتل فرعية'],
                ['القطعة', 'المنتج النهائي'], ['قطعة', 'منتج نهائي'], ['القطع', 'المنتجات النهائية'], ['قطع', 'منتجات نهائية']],
            type4: [['الكتلة', 'المنتج المصدر'], ['الكتل', 'المنتجات المصدر'], ['كتلة', 'منتج مصدر'], ['كتل', 'منتجات مصدر'],
                ['القطعة', 'المنتج المستهدف'], ['قطعة', 'منتج مستهدف'], ['القطع', 'المنتجات المستهدفة'], ['قطع', 'منتجات مستهدفة']]
        }
    };
    var VOCAB_CACHE = {};
    function vocabRe(lang, mode) {
        var key = lang + '|' + mode;
        if (VOCAB_CACHE[key]) return VOCAB_CACHE[key];
        var rules = (VOCAB[lang] || {})[mode];
        if (!rules) return (VOCAB_CACHE[key] = null);
        var map = {};
        rules.forEach(function (r) { if (!(r[0] in map)) map[r[0]] = r[1]; });
        var keys = Object.keys(map).sort(function (a, b) { return b.length - a.length; })
            .map(function (k) { return k.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); });
        return (VOCAB_CACHE[key] = { re: new RegExp(keys.join('|'), 'g'), map: map });
    }
    var PROTECT = ['name', 'sname', 'pname', 'names', 'm'];

    function tx(lang, key, p) {
        var v = (TXT[lang] && TXT[lang][key] != null) ? TXT[lang][key] : TXT.fr[key];
        return typeof v === 'function' ? v(p || {}) : v;
    }

    /* ------------------------------------------------------------------ */
    /* Interface                                                           */
    /* ------------------------------------------------------------------ */

    /* ------------------------- Aides visuelles ------------------------- */

    // Une couleur par sens : Longueur (X) orange, Hauteur (Y) vert, Profondeur (Z) violet.
    var AXC = ['#ff8c00', '#16a34a', '#7c3aed'];
    var AXH = [0xff8c00, 0x16a34a, 0x7c3aed];

    function shade(hex, f) {
        var n = parseInt(String(hex).replace('#', ''), 16), t = f < 0 ? 0 : 255, a = Math.abs(f);
        var c = [n >> 16, (n >> 8) & 255, n & 255].map(function (v) { return Math.round((t - v) * a + v); });
        return 'rgb(' + c.join(',') + ')';
    }

    /** Petite boîte isométrique aux proportions réelles (L × H × P). */
    function isoBox(dims, color, px) {
        var w = dims[0], h = dims[1], d = dims[2], fl = Math.max(w, h, d) * 0.08;
        w = Math.max(w, fl); h = Math.max(h, fl); d = Math.max(d, fl);
        var dx = d * 0.45, dy = d * 0.26, vw = w + dx, vh = h + dy;
        var front = '0,' + dy + ' ' + w + ',' + dy + ' ' + w + ',' + (h + dy) + ' 0,' + (h + dy);
        var top = '0,' + dy + ' ' + dx + ',0 ' + vw + ',0 ' + w + ',' + dy;
        var side = w + ',' + dy + ' ' + vw + ',0 ' + vw + ',' + h + ' ' + w + ',' + (h + dy);
        var st = ' stroke="rgba(15,23,42,.55)" stroke-width="1.2" vector-effect="non-scaling-stroke" stroke-linejoin="round"';
        return '<svg class="co-iso" viewBox="0 0 ' + vw + ' ' + vh + '" style="width:' + (px || 64) + 'px;height:' + Math.round((px || 64) * 0.78) + 'px" aria-hidden="true">' +
            '<polygon points="' + front + '" fill="' + color + '"' + st + '/><polygon points="' + top + '" fill="' + shade(color, 0.45) + '"' + st + '/>' +
            '<polygon points="' + side + '" fill="' + shade(color, -0.3) + '"' + st + '/></svg>';
    }

    /** Barre empilée : pièces (couleur du produit) · chute récupérable (bleu) · perte (rouge). */
    function stackBar(blocks, r) {
        var V = 0, used = 0, rec = 0, byT = {};
        blocks.forEach(function (b) {
            V += vol(b.dims); used += b.used;
            b.placed.forEach(function (p) { byT[p.t] = (byT[p.t] || 0) + p.w * p.h * p.d; });
            b.remnants.forEach(function (x) { if (x.recoverable) rec += vol(x.s); });
        });
        if (!V) return '';
        var segs = Object.keys(byT).map(function (t) { return { c: r.types[t].color, v: byT[t] }; });
        segs.push({ c: '#60a5fa', v: rec });
        segs.push({ c: '#ef4444', v: Math.max(0, V - used - rec) });
        return '<div class="co-sbar" dir="ltr">' + segs.filter(function (s) { return s.v > 0; }).map(function (s) {
            return '<i style="width:' + (s.v / V * 100).toFixed(2) + '%;background:' + s.c + '"></i>';
        }).join('') + '</div>';
    }

    /**
     * Mini plan 2D d'une étape. view = 'face' (Longueur × Hauteur) ou 'top' (Longueur × Profondeur).
     * Montre : bloc, pièces déjà coupées (pâles), zone de travail (jaune), pièce à couper, traits de coupe.
     */
    function planSvg(b, k, r, view) {
        var A = view === 'face' ? [0, 1] : [0, 2], BW = b.dims[A[0]], BH = b.dims[A[1]];
        var pad = Math.max(BW, BH) * 0.03;
        var NS = ' vector-effect="non-scaling-stroke"';
        function rc(pos, size, extra) {
            var y = view === 'face' ? BH - pos[1] - size[1] : pos[A[1]];
            return '<rect x="' + pos[A[0]] + '" y="' + y + '" width="' + size[A[0]] + '" height="' + size[A[1]] + '"' + NS + ' ' + extra + '/>';
        }
        var cur = b.placed[k], out = '';
        out += '<rect x="0" y="0" width="' + BW + '" height="' + BH + '" fill="#f8fafc" stroke="#475569" stroke-width="1.5"' + NS + '/>';
        for (var i = 0; i < k; i++) {
            var q = b.placed[i];
            out += rc([q.x, q.y, q.z], [q.w, q.h, q.d], 'fill="' + r.types[q.t].color + '" fill-opacity=".28" stroke="' + r.types[q.t].color + '" stroke-width="1" stroke-opacity=".6"');
        }
        out += rc(cur.box.p, cur.box.s, 'fill="#facc15" fill-opacity=".22" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="5 3"');
        out += rc([cur.x, cur.y, cur.z], [cur.w, cur.h, cur.d], 'fill="' + r.types[cur.t].color + '" fill-opacity=".95" stroke="#0f172a" stroke-width="1.6"');
        cur.cuts.forEach(function (c) {
            var line = '';
            if (c.axis === A[0]) {
                var y1 = view === 'face' ? BH - cur.box.p[1] - cur.box.s[1] : cur.box.p[A[1]], y2 = y1 + cur.box.s[A[1]];
                line = '<line x1="' + c.at + '" y1="' + y1 + '" x2="' + c.at + '" y2="' + y2 + '"';
            } else if (c.axis === A[1]) {
                var yy = view === 'face' ? BH - c.at : c.at;
                line = '<line x1="' + cur.box.p[A[0]] + '" y1="' + yy + '" x2="' + (cur.box.p[A[0]] + cur.box.s[A[0]]) + '" y2="' + yy + '"';
            }
            if (line) out += line + ' stroke="' + AXC[c.axis] + '" stroke-width="3" stroke-dasharray="7 4"' + NS + '/>';
        });
        return '<svg class="co-plan" viewBox="' + (-pad) + ' ' + (-pad) + ' ' + (BW + 2 * pad) + ' ' + (BH + 2 * pad) + '" preserveAspectRatio="xMidYMid meet" aria-hidden="true">' + out + '</svg>';
    }

    var CSS = '' +
        '.co-flow{border:1px solid #bfdbfe;background:linear-gradient(180deg,#f8fbff,#eef5ff);border-radius:12px;padding:12px 14px;margin-bottom:14px}' +
        '.co-flow:empty{display:none}.co-flow-t{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#1e3a8a!important;margin-bottom:8px}' +
        '.co-flow-row{display:flex;flex-wrap:wrap;align-items:stretch;gap:10px}.co-flow-arrow{display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:22px;color:#2563eb!important;min-width:40px}' +
        '.co-flow-arrow small{font-size:11px;font-weight:700}.co-rtl .co-flow-arrow i{transform:scaleX(-1)}' +
        '.co-cards{display:flex;flex-wrap:wrap;gap:8px;flex:1}' +
        '.co-card{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dbe4f0;border-radius:10px;padding:6px 10px;min-width:150px}' +
        '.co-card.src{border-color:#94a3b8;background:#f8fafc}' +
        '.co-card-t b{display:block;font-size:18px;line-height:1.1}.co-card-t span{display:block;font-size:12px;font-weight:700;line-height:1.3}.co-card-t small{display:block;font-size:11px;color:#475569!important;direction:ltr;unicode-bidi:isolate}' +
        '.co-card .co-ico{width:44px;height:34px;border-radius:6px;flex:none}.co-ico.rec{background:repeating-linear-gradient(45deg,rgba(96,165,250,.35),rgba(96,165,250,.35) 5px,rgba(96,165,250,.7) 5px,rgba(96,165,250,.7) 8px);border:2px solid #2563eb}' +
        '.co-ico.loss{background:repeating-linear-gradient(45deg,rgba(239,68,68,.3),rgba(239,68,68,.3) 5px,rgba(239,68,68,.65) 5px,rgba(239,68,68,.65) 8px);border:2px solid #dc2626}' +
        '.co-sbar{display:flex;height:12px;border-radius:6px;overflow:hidden;background:#e2e8f0;border:1px solid #cbd5e1;min-width:90px}.co-sbar i{display:block;height:100%}' +
        '.co-flow .co-sbar{height:16px;margin-top:10px}' +
        '.co-axes{position:absolute;top:8px;inset-inline-start:8px;display:flex;flex-direction:column;gap:3px;z-index:4;pointer-events:none}' +
        '.co-axchip{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.92);border:1px solid #cbd5e1;border-radius:6px;padding:2px 8px;font-size:12px;font-weight:700;width:max-content}' +
        '.co-axchip i{display:inline-block;width:16px;height:5px;border-radius:3px}.co-axchip span{direction:ltr;unicode-bidi:isolate}' +
        '.co-player{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:8px 10px;background:#fff;border-top:1px solid #e2e8f0}' +
        '.co-player .btn{min-width:34px;padding:2px 8px}.co-player input[type=range]{flex:1;min-width:120px;direction:ltr}' +
        '.co-slabel{font-weight:800;min-width:96px;text-align:center;white-space:nowrap}' +
        '.co-sc{display:none;gap:12px;flex-wrap:wrap;padding:10px;background:#fffdf3;border-top:1px solid #fde68a}.co-sc.on{display:flex}' +
        '.co-sc-hint{padding:8px 12px;font-size:13px;background:#f8fafc;border-top:1px solid #e2e8f0;color:#475569!important}' +
        '.co-sc figure{margin:0;width:200px;max-width:100%;text-align:center}.co-sc figcaption{font-size:11px;font-weight:700;color:#475569!important;margin-top:2px}' +
        '.co-plan{width:100%;height:auto;max-height:150px;display:block;background:#fff;border:1px solid #e2e8f0;border-radius:6px}' +
        '.co-sc-info{flex:1;min-width:200px;display:flex;flex-direction:column;gap:8px;justify-content:center}' +
        '.co-sc-title{display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:15px}.co-sc-title small{direction:ltr;unicode-bidi:isolate;color:#475569!important}' +
        '.co-chips{display:flex;flex-wrap:wrap;gap:6px}' +
        '.co-chip{display:inline-flex;align-items:center;gap:6px;background:#fff;border:2px solid;border-radius:20px;padding:3px 11px;font-size:13px;font-weight:700}.co-chip em{font-style:normal;direction:ltr;unicode-bidi:isolate}' +
        '.co-key{display:flex;flex-wrap:wrap;gap:4px 12px;font-size:11px;color:#475569!important}.co-key i{display:inline-block;width:10px;height:10px;border-radius:2px;margin:0 4px;vertical-align:-1px}' +
        '.co-slist{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:8px;max-height:420px;overflow:auto;padding:2px}' +
        '.co-sitem{display:flex;flex-direction:column;gap:5px;border:2px solid #e2e8f0;border-radius:10px;padding:6px 8px;background:#fff;cursor:pointer}' +
        '.co-sitem:hover{border-color:#93c5fd}.co-sitem.on{border-color:#2563eb;background:#eff6ff}' +
        '.co-sitem-h{display:flex;align-items:center;gap:6px;font-size:13px}.co-sitem-h b{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
        '.co-sitem .co-chips{gap:4px}.co-sitem .co-chip{padding:1px 8px;font-size:11px;border-width:1.5px}' +
        '.co-whyt{cursor:pointer;user-select:none}.co-whyt:after{content:" ▾";float:inline-end}.co-why.shut .co-body{display:none}.co-why.shut .co-whyt:after{content:" ▸"}' +
        '.co-wrap{font-size:14px}.co-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,1fr);gap:16px}' +
        '@media(max-width:1100px){.co-grid{grid-template-columns:1fr}}' +
        '.co-top{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px}.co-top .co-sp{flex:1}' +
        '.co-lang{display:inline-flex;direction:ltr}.co-lang button{border:1px solid #2563eb;background:#fff;padding:3px 12px;font-size:13px;cursor:pointer}' +
        '.co-lang button:first-child{border-radius:6px 0 0 6px}.co-lang button:last-child{border-radius:0 6px 6px 0}' +
        '.co-lang button.on{background:#2563eb;color:#fff!important}.co-lang button.on *{color:#fff!important}' +
        '.co-helpbox{display:none;border:1px solid #bfdbfe;background:#eff6ff;border-radius:10px;padding:12px 14px;margin-bottom:14px}' +
        '.co-helpbox.open{display:block}.co-helpbox dl{margin:0;display:grid;grid-template-columns:minmax(150px,230px) 1fr;gap:6px 14px}' +
        '.co-helpbox dt{font-weight:800}.co-helpbox dd{margin:0;line-height:1.5}' +
        '@media(max-width:800px){.co-helpbox dl{grid-template-columns:1fr}}' +
        '.co-viewer{position:relative;background:linear-gradient(180deg,#f8fafc,#e2e8f0);border:1px solid #cbd5e1;border-radius:10px;overflow:hidden}' +
        '.co-canvas{width:100%;height:480px;direction:ltr}.co-canvas canvas{display:block}' +
        '.co-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:8px 10px;background:#fff;border-bottom:1px solid #e2e8f0}' +
        '.co-toolbar label{margin:0;display:flex;align-items:center;gap:5px;white-space:nowrap}' +
        '.co-toolbar input[type=range]{width:110px;direction:ltr}' +
        '.co-tip{position:absolute;pointer-events:none;background:rgba(15,23,42,.92);color:#fff;padding:6px 9px;border-radius:6px;font-size:12px;display:none;z-index:5;white-space:nowrap}' +
        '.co-legend{display:flex;flex-wrap:wrap;gap:6px 14px;padding:8px 10px;background:#fff;border-top:1px solid #e2e8f0}' +
        '.co-sw{display:inline-block;width:12px;height:12px;border-radius:3px;margin:0 5px;vertical-align:-1px}' +
        '.co-sw.rec{background:rgba(59,130,246,.45);border:2px solid #2563eb}.co-sw.loss{background:rgba(239,68,68,.5);border:2px solid #dc2626}' +
        '.co-kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:12px}' +
        '.co-kpi{border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;background:#fff}' +
        '.co-kpi small{display:block;font-weight:600}.co-kpi b{font-size:17px}' +
        '.co-kpi.good b{color:#059669!important}.co-kpi.warn b{color:#d97706!important}.co-kpi.bad b{color:#dc2626!important}' +
        '.co-panel{border:1px solid #e2e8f0;border-radius:8px;background:#fff;margin-bottom:12px}' +
        '.co-panel h6{margin:0;padding:8px 10px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-size:12px;text-transform:uppercase;letter-spacing:.04em}' +
        '.co-panel .co-body{padding:8px 10px}' +
        '.co-tbl{width:100%;font-size:12px}.co-tbl th{font-weight:600;padding:3px 4px}.co-tbl td{padding:2px 3px}' +
        '.co-tbl input{width:100%;min-width:52px;padding:2px 5px;border:1px solid #cbd5e1;border-radius:4px;font-size:12px;direction:ltr}' +
        '.co-tbl input.co-name{min-width:90px}' +
        '.co-params{display:flex;flex-direction:column;gap:12px}.co-param label{display:flex;align-items:center;gap:8px;margin:0 0 3px;font-weight:700}' +
        '.co-pd{font-size:13px;line-height:1.5;color:#475569!important}.co-miss{color:#dc2626!important}' +
        '.co-tbl td{padding:5px 6px;border-top:1px solid #f1f5f9;font-size:13px}.co-tbl th{padding:4px 6px;text-align:start}' +
        '.co-params input[type=number]{width:70px;padding:2px 5px;border:1px solid #cbd5e1;border-radius:4px;direction:ltr}' +
        '.co-warn{background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:8px 10px;margin-bottom:10px}' +
        '.co-bar{height:8px;border-radius:4px;background:#e2e8f0;overflow:hidden;min-width:70px}.co-bar>i{display:block;height:100%;background:#10b981}' +
        '.co-blocks tr{cursor:pointer}.co-blocks tr.active{background:#eff6ff}' +
        '.co-wrap,.co-wrap *{color:#0f172a}.co-wrap .co-run,.co-wrap .co-run *,.co-wrap .co-tip,.co-wrap .co-tip *,.co-wrap .co-num,.co-wrap .badge{color:#fff!important}.co-wrap .text-muted,.co-kpi small,.co-tbl th,.co-sub{color:#475569!important}' +
        '.co-h{font-size:14px;font-weight:800;margin:4px 0 8px;text-transform:uppercase;letter-spacing:.03em;color:#1e3a8a!important}' +
        '.co-list,.co-steps{list-style:none;padding:0;margin:0 0 14px}.co-line{font-size:14px;margin-bottom:8px;line-height:1.5}' +
        '.co-sub{font-size:13px;line-height:1.5;margin-top:2px}' +
        '.co-step{border:1px solid #e2e8f0;border-inline-start:4px solid #2563eb;border-radius:8px;padding:8px 12px;margin-bottom:8px;background:#f8fafc;font-size:14px}' +
        '.co-step-h{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:2px}' +
        '.co-num{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#2563eb;color:#fff!important;font-size:12px}' +
        '.co-cut{font-size:14px;margin-top:4px}' +
        '.co-help{font-size:13px;line-height:1.5;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 10px;margin-top:10px}' +
        '.co-why{background:#fffbeb;border-color:#fcd34d}.co-why h6{background:#fef3c7}.co-why ul{list-style:none;padding:0;margin:0}.co-why li{margin-bottom:8px;line-height:1.55;font-size:14px}' +
        '.co-hint{padding:26px 16px;text-align:center;font-size:14px;color:#475569}' +
        '.co-rtl,.co-rtl *:not(i):not(.fas):not(.fa):not(.far):not(.fab){font-family:"Segoe UI",Tahoma,"Noto Sans Arabic","Noto Naskh Arabic","DejaVu Sans",Arial,sans-serif!important;font-weight:500!important;letter-spacing:0!important}' +
        '.co-rtl b,.co-rtl strong,.co-rtl dt,.co-rtl .co-h,.co-rtl h6,.co-rtl th,.co-rtl .co-kpi b,.co-rtl .co-lang button{font-weight:700!important}' +
        '.co-rtl{direction:rtl;text-align:right}.co-rtl .text-end{text-align:left!important}' +
        '.co-busy{position:absolute;inset:0;background:rgba(255,255,255,.7);display:none;align-items:center;justify-content:center;z-index:6;font-weight:700}';

    function injectCss() {
        if (document.getElementById('co-css')) return;
        var s = document.createElement('style');
        s.id = 'co-css'; s.textContent = CSS; document.head.appendChild(s);
    }

    function CuttingPlanner(root, config) {
        this.root = root;
        this.config = config || { sources: [], pieces: [] };
        this.result = null;
        this.warns = [];
        this.current = 0;
        this.state = { explode: 0, clip: 100, showRemnants: true, wire: false, rotate: false, step: 0 };
        try { this.lang = localStorage.getItem('co_lang') === 'ar' ? 'ar' : 'fr'; } catch (e) { this.lang = 'fr'; }
        injectCss();
        this.data = { sources: this.config.sources || [], pieces: this.config.pieces || [] };
        this.renderShell();
        this.initViewer();
        this.applyLang();
        this.optimize();
    }

    CuttingPlanner.prototype.t = function (key, p) {
        var v = vocabRe(this.lang, this.config && this.config.mode);
        if (!v) return tx(this.lang, key, p);
        var tokens = [], q = p;
        if (p) {
            q = {};
            Object.keys(p).forEach(function (k) {
                q[k] = p[k];
                if (PROTECT.indexOf(k) !== -1 && typeof p[k] === 'string') { tokens.push(p[k]); q[k] = '\u0001' + (tokens.length - 1) + '\u0002'; }
            });
        }
        var conv = function (str) {
            return typeof str !== 'string' ? str : str.replace(v.re, function (m) { return v.map[m]; })
                .replace(/\u0001(\d+)\u0002/g, function (_, i) { return tokens[i]; });
        };
        var out = tx(this.lang, key, q);
        return Array.isArray(out) ? out.map(function (x) { return Array.isArray(x) ? x.map(conv) : conv(x); }) : conv(out);
    };

    CuttingPlanner.prototype.rowSource = function (s) { return this.row(s, 'co-src', 'Bloc'); };
    CuttingPlanner.prototype.rowPiece = function (p) { return this.row(p, 'co-pc', 'Pièce'); };
    CuttingPlanner.prototype.row = function (x, cls, label) {
        var d = function (v) { return v > 0 ? '<span dir="ltr">' + fmt(v, 2) + '</span>' : '<span class="co-miss">—</span>'; };
        return '<tr class="' + cls + '"><td>' + esc(x.name || label) + '</td><td>' + d(x.w) + '</td><td>' + d(x.h) + '</td><td>' + d(x.d) + '</td><td><b>' + fmt(x.qty || 1, 0) + '</b></td></tr>';
    };

    CuttingPlanner.prototype.renderShell = function () {
        var self = this, cfg = this.config;
        var i = function (k) { return '<span data-i18n="' + k + '"></span>'; };
        var head = '<thead><tr><th>' + i('th_name') + '</th><th>' + i('th_len') + '</th><th>' + i('th_h') + '</th><th>' + i('th_depth') + '</th><th>' + i('th_qty') + '</th></tr></thead>';
        this.root.innerHTML =
            '<div class="co-wrap">' +
            '<div class="co-top"><button type="button" class="btn btn-sm btn-outline-primary co-help-btn"><i class="fas fa-circle-question me-1"></i>' + i('help_btn') + '</button><span class="co-sp"></span>' +
            '<div class="co-lang"><button type="button" data-lang="fr">FR</button><button type="button" data-lang="ar">العربية</button></div></div>' +
            '<div class="co-helpbox"></div>' +
            '<div class="co-flow"></div>' +
            '<div class="co-grid"><div>' +
            '<div class="co-viewer"><div class="co-toolbar">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-prev">‹</button>' +
            '<select class="form-select form-select-sm co-block-select" style="width:auto"></select>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-next">›</button>' +
            '<label>' + i('explode') + ' <input type="range" class="co-explode" min="0" max="100" value="0"></label>' +
            '<label>' + i('clip') + ' <input type="range" class="co-clip" min="5" max="100" value="100"></label>' +
            '<label><input type="checkbox" class="co-rem" checked> ' + i('chutes') + '</label>' +
            '<label><input type="checkbox" class="co-wire"> ' + i('wire') + '</label>' +
            '<label><input type="checkbox" class="co-spin"> ' + i('spin') + '</label>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-reset">' + i('reset') + '</button></div>' +
            '<div style="position:relative"><div class="co-canvas"></div><div class="co-axes"></div><div class="co-tip"></div><div class="co-busy"></div></div>' +
            '<div class="co-player" dir="ltr">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-sfirst" data-i18n-title="step_first">⏮</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-sprev" data-i18n-title="step_prev">◀</button>' +
            '<button type="button" class="btn btn-sm btn-primary co-play" data-i18n-title="step_play">▶</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary co-snext" data-i18n-title="step_next">▶|</button>' +
            '<input type="range" class="co-slider" min="0" max="0" value="0"><span class="co-slabel"></span></div>' +
            '<div class="co-sc-hint"></div><div class="co-sc"></div>' +
            '<div class="co-legend"></div></div>' +
            '<div class="co-panel mt-3"><h6>' + i('blocks_used') + '</h6><div class="co-body p-0"><div class="table-responsive"><table class="table table-sm mb-0 co-blocks"><thead><tr>' +
            '<th>' + i('th_num') + '</th><th>' + i('th_block') + '</th><th class="text-end">' + i('th_pieces') + '</th><th>' + i('th_eff') + '</th><th class="text-end">' + i('th_chute') + '</th></tr></thead><tbody></tbody></table></div></div></div>' +
            '</div><div>' +
            '<div class="co-warnings"></div><div class="co-kpis"></div>' +
            '<div class="co-panel"><h6>' + i('params') + '</h6><div class="co-body co-params">' +
            '<div class="co-param"><label>' + i('kerf') + ' <input type="number" class="co-kerf" value="0" min="0" step="0.5"></label><div class="co-pd" data-i18n="kerf_d"></div></div>' +
            '<div class="co-param"><label>' + i('minrec') + ' <input type="number" class="co-minrec" value="10" min="1" step="1"></label><div class="co-pd" data-i18n="minrec_d"></div></div>' +
            '<div class="co-param"><label><input type="checkbox" class="co-rot" checked> ' + i('rot') + '</label><div class="co-pd" data-i18n="rot_d"></div></div>' +
            '<div class="co-param"><button type="button" class="btn btn-primary btn-sm co-run"><i class="fas fa-bolt me-1"></i>' + i('run') + '</button><div class="co-pd" data-i18n="run_d"></div></div>' +
            '</div></div>' +
            '<div class="co-panel"><h6>' + i('src_title') + '</h6><div class="co-body"><table class="co-tbl co-src-tbl">' + head + '<tbody>' +
            (cfg.sources || []).map(function (s) { return self.rowSource(s); }).join('') + '</tbody></table></div></div>' +
            '<div class="co-panel"><h6>' + i('pc_title') + '</h6><div class="co-body"><table class="co-tbl co-pc-tbl">' + head + '<tbody>' +
            (cfg.pieces || []).map(function (p) { return self.rowPiece(p); }).join('') + '</tbody></table></div></div>' +
            '</div></div>' +
            '<div class="co-panel co-why shut mt-3" style="display:none"><h6 class="co-why-t co-whyt"></h6><div class="co-body"><ul class="co-why-b"></ul></div></div>' +
            '<div class="co-panel mt-3"><h6>' + i('detail_title') + '</h6><div class="co-body co-detail"></div></div></div>';

        var q = function (s) { return self.root.querySelector(s); };
        this.el = {
            canvas: q('.co-canvas'), tip: q('.co-tip'), busy: q('.co-busy'), legend: q('.co-legend'),
            kpis: q('.co-kpis'), warnings: q('.co-warnings'), blocks: q('.co-blocks tbody'), detail: q('.co-detail'),
            select: q('.co-block-select'), why: q('.co-why'), whyT: q('.co-why-t'), whyB: q('.co-why-b'), help: q('.co-helpbox'), wrap: q('.co-wrap'),
            flow: q('.co-flow'), axes: q('.co-axes'), player: q('.co-player'), slider: q('.co-slider'), slabel: q('.co-slabel'), play: q('.co-play'),
            sc: q('.co-sc'), schint: q('.co-sc-hint')
        };

        q('.co-sfirst').addEventListener('click', function () { self.setStep(0); });
        q('.co-sprev').addEventListener('click', function () { self.setStep(self.state.step - 1); });
        q('.co-snext').addEventListener('click', function () { self.setStep(self.state.step + 1); });
        this.el.play.addEventListener('click', function () { self.togglePlay(); });
        this.el.slider.addEventListener('input', function () { self.setStep(parseInt(this.value, 10)); });
        this.el.whyT.addEventListener('click', function () { self.el.why.classList.toggle('shut'); });
        this.el.detail.addEventListener('click', function (e) {
            var it = e.target.closest('[data-step]');
            if (it) { self.setStep(parseInt(it.getAttribute('data-step'), 10)); self.el.canvas.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
        });

        q('.co-run').addEventListener('click', function () { self.optimize(); });
        q('.co-prev').addEventListener('click', function () { self.show(self.current - 1); });
        q('.co-next').addEventListener('click', function () { self.show(self.current + 1); });
        this.el.select.addEventListener('change', function () { self.show(parseInt(this.value, 10)); });
        q('.co-explode').addEventListener('input', function () { self.state.explode = this.value / 100; self.buildScene(); });
        q('.co-clip').addEventListener('input', function () { self.state.clip = +this.value; self.buildScene(); });
        q('.co-rem').addEventListener('change', function () { self.state.showRemnants = this.checked; self.buildScene(); });
        q('.co-wire').addEventListener('change', function () { self.state.wire = this.checked; self.buildScene(); });
        q('.co-spin').addEventListener('change', function () { if (self.controls) self.controls.autoRotate = this.checked; });
        q('.co-reset').addEventListener('click', function () { self.fitCamera(); });
        q('.co-help-btn').addEventListener('click', function () { self.el.help.classList.toggle('open'); });
        this.root.querySelectorAll('.co-lang button').forEach(function (b) {
            b.addEventListener('click', function () {
                self.lang = b.getAttribute('data-lang');
                try { localStorage.setItem('co_lang', self.lang); } catch (e) { }
                self.applyLang();
                self.renderResult(true);
            });
        });
        this.el.blocks.addEventListener('click', function (e) {
            var tr = e.target.closest('tr[data-i]');
            if (tr) self.show(parseInt(tr.getAttribute('data-i'), 10));
        });
    };

    CuttingPlanner.prototype.applyLang = function () {
        var self = this;
        this.el.wrap.classList.toggle('co-rtl', this.lang === 'ar');
        this.el.wrap.setAttribute('dir', this.lang === 'ar' ? 'rtl' : 'ltr');
        this.root.querySelectorAll('[data-i18n]').forEach(function (n) { n.textContent = self.t(n.getAttribute('data-i18n')); });
        this.root.querySelectorAll('[data-i18n-title]').forEach(function (n) { n.title = self.t(n.getAttribute('data-i18n-title')); });
        this.root.querySelectorAll('.co-lang button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-lang') === self.lang); });
        this.el.help.innerHTML = '<div class="co-h">' + this.t('h_title') + '</div><dl>' +
            this.t('h').map(function (x) { return '<dt>' + esc(x[0]) + '</dt><dd>' + esc(x[1]) + '</dd>'; }).join('') + '</dl>';
        this.el.busy.textContent = this.t('busy');
    };

    /** Remplace blocs/pièces (formulaire en direct) puis recalcule, avec un léger délai. */
    CuttingPlanner.prototype.update = function (config) {
        var self = this;
        var modeChanged = (this.config && this.config.mode) !== config.mode;
        this.config = config;
        if (modeChanged) this.applyLang();
        this.data = { sources: config.sources || [], pieces: config.pieces || [] };
        var q = function (sel) { return self.root.querySelector(sel); };
        q('.co-src-tbl tbody').innerHTML = (config.sources || []).map(function (x) { return self.rowSource(x); }).join('');
        q('.co-pc-tbl tbody').innerHTML = (config.pieces || []).map(function (x) { return self.rowPiece(x); }).join('');
        clearTimeout(this._t);
        this._t = setTimeout(function () { self.optimize(); }, 350);
    };

    CuttingPlanner.prototype.readTable = function (sel) {
        var list = sel.indexOf('src') !== -1 ? this.data.sources : this.data.pieces;
        return list.map(function (x) {
            return { name: x.name || '', w: +x.w || 0, h: +x.h || 0, d: +x.d || 0, qty: +x.qty || 0 };
        });
    };

    CuttingPlanner.prototype.optimize = async function () {
        var self = this;
        var allSources = this.readTable('.co-src-tbl');
        var sources = allSources.filter(function (s) { return s.w > 0 && s.h > 0 && s.d > 0 && s.qty > 0; });
        var allPieces = this.readTable('.co-pc-tbl');
        var pieces = allPieces.filter(function (p) { return p.w > 0 && p.h > 0 && p.d > 0 && p.qty > 0; });
        var missing = allPieces.filter(function (p) { return p.qty > 0 && !(p.w > 0 && p.h > 0 && p.d > 0); });

        var warn = [];
        if (missing.length) warn.push({ k: 'w_missing', p: { names: missing.map(function (p) { return p.name; }).join(', ') } });
        if (!sources.length) warn.push({ k: 'w_nosrc' });
        if (!pieces.length) warn.push({ k: 'w_nopc' });
        var total = pieces.reduce(function (s, p) { return s + p.qty; }, 0);
        if (total > 20000) warn.push({ k: 'w_toomany', p: { n: total } });

        if (this.config.live && (!allPieces.length || !allSources.length)) {
            this.result = null; this.warns = []; this.hintOnly = true;
            this.renderResult();
            return;
        }
        this.hintOnly = false;
        if (!sources.length || !pieces.length || total > 20000) {
            this.result = null; this.warns = warn;
            this.renderResult();
            return;
        }

        this.el.busy.style.display = 'flex';
        await tick();
        try {
            var res = await solve(sources, pieces, {
                kerf: (parseFloat(this.root.querySelector('.co-kerf').value) || 0) / 10,
                rotate: this.root.querySelector('.co-rot').checked,
                minRecover: parseFloat(this.root.querySelector('.co-minrec').value) || 10
            }, function (done, all, nb) {
                self.el.busy.textContent = self.t('busy_n', { done: done, all: all, nb: nb });
            });
            res.sources = sources;
            this.result = res;
            this.warns = warn.concat(res.warnings);
            this.current = 0;
        } catch (err) {
            console.error(err);
            this.result = null;
            warn.push({ k: 'w_err', p: { m: err.message } });
            this.warns = warn;
        }
        this.el.busy.style.display = 'none';
        this.el.busy.textContent = this.t('busy');
        this.renderResult();
    };

    CuttingPlanner.prototype.renderResult = function (keepCurrent) {
        var self = this, r = this.result, el = this.el, t = function (k, p) { return self.t(k, p); };
        el.warnings.innerHTML = this.warns.map(function (w) { return '<div class="co-warn">⚠ ' + esc(t(w.k, w.p)) + '</div>'; }).join('');

        if (!r) {
            el.kpis.innerHTML = ''; el.blocks.innerHTML = ''; el.legend.innerHTML = ''; el.select.innerHTML = '';
            el.why.style.display = 'none'; el.flow.innerHTML = ''; el.axes.innerHTML = '';
            this.stopPlay(); this.state.step = 0; this.syncPlayer();
            el.detail.innerHTML = this.hintOnly ? '<div class="co-hint">' + esc(t('hint')) + '</div>' : '';
            this.buildScene();
            return;
        }
        var T = r.totals, UM = t('u_m3'), UC = t('u_cm');
        var effCls = T.efficiency >= 85 ? 'good' : T.efficiency >= 70 ? 'warn' : 'bad';
        var overBlocks = T.blocksUsed > T.blocksAvailable;
        var recPct = T.blockVol ? T.recoverVol / T.blockVol * 100 : 0;
        el.kpis.innerHTML =
            '<div class="co-kpi ' + effCls + '"><small>' + t('kpi_eff') + '</small><b>' + fmt(T.efficiency, 1) + ' %</b></div>' +
            '<div class="co-kpi ' + (overBlocks ? 'bad' : 'good') + '"><small>' + t('kpi_blocks') + '</small><b>' + T.blocksUsed + ' / ' + T.blocksAvailable + ' ' + t('avail') + '</b><small>' + t('kpi_min', { n: T.theoreticalMin }) + '</small></div>' +
            '<div class="co-kpi ' + (T.pieceCount < T.pieceTarget ? 'bad' : 'good') + '"><small>' + t('kpi_pieces') + '</small><b>' + T.pieceCount + ' / ' + T.pieceTarget + '</b></div>' +
            '<div class="co-kpi"><small>' + t('kpi_vol') + '</small><b>' + fmt(m3(T.pieceVol), 3) + ' ' + UM + '</b><small>' + t('kpi_on', { v: fmt(m3(T.blockVol), 3) }) + '</small></div>' +
            '<div class="co-kpi"><small>' + t('kpi_rec') + '</small><b style="color:#2563eb">' + fmt(m3(T.recoverVol), 3) + ' ' + UM + '</b><small>' + t('kpi_rec_sub', { pct: fmt(recPct, 1) }) + '</small></div>' +
            '<div class="co-kpi ' + (T.efficiency >= 85 ? '' : 'warn') + '"><small>' + t('kpi_loss') + '</small><b>' + fmt(m3(T.wasteVol - T.recoverVol), 3) + ' ' + UM + '</b><small>' + t('kpi_loss_sub', { v: fmt(m3(T.kerfVol), 3) }) + '</small></div>';

        el.legend.innerHTML = r.types.map(function (ty) {
            return '<span><i class="co-sw" style="background:' + ty.color + '"></i>' + esc(ty.name) + ' <small class="text-muted">' + ty.dims.join('×') + ' cm</small></span>';
        }).join('') + '<span><i class="co-sw rec"></i>' + t('legend_rec') + '</span><span><i class="co-sw loss"></i>' + t('legend_loss') + '</span>';

        el.select.innerHTML = r.blocks.map(function (b, i) {
            return '<option value="' + i + '">' + esc(t('block_opt', { i: i + 1, n: r.blocks.length, extra: b.extra })) + '</option>';
        }).join('');

        el.blocks.innerHTML = r.blocks.map(function (b, i) {
            var eff = vol(b.dims) ? b.used / vol(b.dims) * 100 : 0;
            return '<tr data-i="' + i + '"><td>' + (i + 1) + '</td><td>' + esc((r.sources[b.src] || {}).name || 'Bloc') + ' <small class="text-muted">' + b.dims.join('×') + '</small>' +
                (b.extra ? ' <span class="badge bg-danger">' + t('badge_extra') + '</span>' : '') + '</td>' +
                '<td class="text-end">' + b.placed.length + '</td>' +
                '<td>' + stackBar([b], r) + '<small>' + fmt(eff, 1) + ' %</small></td>' +
                '<td class="text-end">' + fmt(m3(vol(b.dims) - b.used), 3) + ' ' + UM + '</td></tr>';
        }).join('');

        this.renderFlow();
        this.renderWhy();
        if (keepCurrent) {
            this.show(this.current, true);
        } else {
            this.show(0);
        }
    };

    CuttingPlanner.prototype.renderWhy = function () {
        var self = this, r = this.result, t = function (k, p) { return self.t(k, p); };
        var T = r.totals, lines = [];
        lines.push(t('y_need', { pcs: T.pieceTarget, v: fmt(m3(r.types.reduce(function (s, ty) { return s + vol(ty.dims) * ty.qty; }, 0)), 3) }));
        (r.capacity || []).forEach(function (c) {
            var s = r.sources[c.src], ty = r.types[c.type], p = {
                sname: s.name, sdims: [s.w, s.h, s.d].join(' × '), sv: fmt(m3(vol([s.w, s.h, s.d])), 3),
                pname: ty.name, pdims: ty.dims.join(' × '), pv: fmt(m3(vol(ty.dims)), 3), cap: c.cap, ratio: fmt(c.ratio, 2), next: fmt(m3(vol(ty.dims) * (c.cap + 1)), 3)
            };
            if (c.many) { lines.push(t('y_many', p)); return; }
            lines.push(t('y_cap', p) + ' ' + (Math.floor(c.ratio + 1e-9) > c.cap ? t('y_shape', p) : t('y_vol', p)));
        });
        lines.push(t('y_result', { n: T.blocksUsed, pcs: T.pieceTarget, min: T.theoreticalMin }));
        if (T.efficiency < 85) {
            lines.push(t('y_eff', { eff: fmt(T.efficiency, 1), rec: fmt(m3(T.recoverVol), 3), pct: fmt(T.blockVol ? T.recoverVol / T.blockVol * 100 : 0, 1) }));
        }
        this.el.whyT.textContent = t('why_title', { n: T.blocksUsed });
        this.el.whyB.innerHTML = lines.map(function (l) { return '<li>' + l + '</li>'; }).join('');
        this.el.why.style.display = '';
    };

    CuttingPlanner.prototype.show = function (i, keep) {
        var r = this.result;
        if (!r || !r.blocks.length) { this.buildScene(); return; }
        this.current = Math.max(0, Math.min(r.blocks.length - 1, i));
        if (!keep) { this.stopPlay(); this.state.step = 0; }
        this.el.select.value = this.current;
        var rows = this.el.blocks.querySelectorAll('tr');
        rows.forEach(function (tr, k) { tr.classList.toggle('active', k === this.current); }, this);
        this.renderDetail();
        this.syncPlayer();
        this.buildScene();
        if (!keep) this.fitCamera();
    };

    /* ------------------- Résumé visuel + lecteur d'étapes ------------------- */

    CuttingPlanner.prototype.card = function (icon, big, name, sub, cls) {
        return '<div class="co-card ' + (cls || '') + '">' + icon + '<div class="co-card-t"><b>' + big + '</b><span>' + esc(name) + '</span>' + (sub ? '<small>' + sub + '</small>' : '') + '</div></div>';
    };

    /** Cartes : [blocs sources] → [pièces obtenues] + chutes + perte, pour une liste de blocs. */
    CuttingPlanner.prototype.cardsFor = function (blocks, withSources) {
        var self = this, r = this.result, t = function (k, p) { return self.t(k, p); }, UM = t('u_m3');
        var dimTxt = function (d) { return d.map(function (v) { return fmt(v, 1); }).join(' × ') + ' ' + t('u_cm'); };
        var srcN = {}, pcN = {}, V = 0, used = 0, rec = 0;
        blocks.forEach(function (b) {
            srcN[b.src] = (srcN[b.src] || 0) + 1; V += vol(b.dims); used += b.used;
            b.placed.forEach(function (p) { pcN[p.t] = (pcN[p.t] || 0) + 1; });
            b.remnants.forEach(function (x) { if (x.recoverable) rec += vol(x.s); });
        });
        var loss = Math.max(0, V - used - rec), html = {};
        html.src = Object.keys(srcN).map(function (si) {
            var s = r.sources[si];
            return self.card(isoBox([s.w, s.h, s.d], '#94a3b8', 70), t('flow_blocks', { n: srcN[si] }), s.name, '<span dir="ltr">' + dimTxt([s.w, s.h, s.d]) + '</span>', 'src');
        }).join('');
        html.pieces = Object.keys(pcN).map(function (ti) {
            var ty = r.types[ti];
            return self.card(isoBox(ty.dims, ty.color, 62), '× ' + fmt(pcN[ti], 0), ty.name, '<span dir="ltr">' + dimTxt(ty.dims) + '</span>');
        }).join('');
        html.scrap = (rec > 0 ? self.card('<div class="co-ico rec"></div>', fmt(m3(rec), 3) + ' ' + UM, t('flow_scrap_rec'), fmt(V ? rec / V * 100 : 0, 1) + ' %') : '') +
            (loss > 0 ? self.card('<div class="co-ico loss"></div>', fmt(m3(loss), 3) + ' ' + UM, t('flow_scrap_loss'), fmt(V ? loss / V * 100 : 0, 1) + ' %') : '');
        return html;
    };

    CuttingPlanner.prototype.renderFlow = function () {
        var r = this.result, t = this.t.bind(this);
        if (!r || !r.blocks.length) { this.el.flow.innerHTML = ''; return; }
        var c = this.cardsFor(r.blocks);
        this.el.flow.innerHTML = '<div class="co-flow-t"><i class="fas fa-diagram-project me-1"></i>' + esc(t('flow_title')) + '</div>' +
            '<div class="co-flow-row"><div class="co-cards">' + c.src + '</div>' +
            '<div class="co-flow-arrow"><i class="fas fa-scissors"></i><i class="fas fa-arrow-right"></i></div>' +
            '<div class="co-cards" style="flex:3">' + c.pieces + c.scrap + '</div></div>' + stackBar(r.blocks, r);
    };

    CuttingPlanner.prototype.axisKey = function () {
        var self = this;
        return '<div class="co-key">' + [0, 1, 2].map(function (a) { return '<span><i style="background:' + AXC[a] + '"></i>' + esc(self.t('side' + a)) + '</span>'; }).join('') + '</div>';
    };

    CuttingPlanner.prototype.chips = function (p) {
        var self = this, UC = this.t('u_cm');
        return p.cuts.map(function (c, k) {
            return '<span class="co-chip" style="border-color:' + AXC[c.axis] + '">✂ ' + esc(self.t('cut_k', { k: k + 1 })) + ' · <span style="color:' + AXC[c.axis] + '!important">' + esc(self.t('side' + c.axis)) + '</span> · ' +
                esc(self.t('from_edge', { at: '⁦' + fmt(c.at, 1) + ' ' + UC + '⁩' })) + '</span>';
        }).join('') || '<span class="co-sub">' + esc(this.t('d_nocut')) + '</span>';
    };

    CuttingPlanner.prototype.renderDetail = function () {
        var self = this, r = this.result, b = r.blocks[this.current], t = function (k, p) { return self.t(k, p); };
        var UC = t('u_cm'), UM = t('u_m3');
        var dimTxt = function (d) { return d.map(function (v) { return fmt(v, 1); }).join(' × ') + ' ' + UC; };
        var sw = function (ti) { return '<i class="co-sw" style="background:' + r.types[ti].color + '"></i>'; };
        var ltr = function (s) { return '<span dir="ltr">' + s + '</span>'; };

        var c = this.cardsFor([b]);
        var recover = b.remnants.filter(function (x) { return x.recoverable; }).sort(function (x, y) { return vol(y.s) - vol(x.s); });
        var remHtml = recover.slice(0, 8).map(function (x) {
            return self.card('<div class="co-ico rec"></div>', ltr(dimTxt(x.s)), t('flow_scrap_rec'), fmt(m3(vol(x.s)), 3) + ' ' + UM);
        }).join('');

        var LIMIT = 300;
        var steps = b.placed.slice(0, LIMIT).map(function (p, n) {
            return '<div class="co-sitem" data-step="' + (n + 1) + '"><div class="co-sitem-h"><span class="co-num">' + (n + 1) + '</span>' + sw(p.t) +
                '<b>' + esc(r.types[p.t].name) + '</b> <small class="co-sub" dir="ltr">' + dimTxt([p.w, p.h, p.d]) + '</small></div>' +
                '<div class="co-chips">' + self.chips(p) + '</div></div>';
        }).join('');

        this.el.detail.innerHTML =
            '<div class="co-h">' + t('this_gives') + '</div>' +
            '<div class="co-cards mb-3">' + c.pieces + c.scrap + '</div>' + stackBar([b], r) +
            (remHtml ? '<div class="co-h mt-3">' + t('d_rec_title') + '</div><div class="co-cards mb-3">' + remHtml + '</div>' : '') +
            '<div class="co-h mt-3">' + t('step_title') + '</div>' + this.axisKey() +
            '<div class="co-slist mt-2">' + steps + (b.placed.length > LIMIT ? '<div class="co-line">' + t('d_more', { n: b.placed.length - LIMIT }) + '</div>' : '') + '</div>';
        this.markStep();
    };

    CuttingPlanner.prototype.markStep = function () {
        var s = this.state.step, list = this.el.detail.querySelector('.co-slist');
        this.el.detail.querySelectorAll('.co-sitem').forEach(function (n) {
            var on = parseInt(n.getAttribute('data-step'), 10) === s;
            n.classList.toggle('on', on);
            if (on && list) list.scrollTop = n.offsetTop - list.offsetTop - 6;
        });
    };

    /** Carte de l'étape courante : plans face + dessus, pièce, coupes. */
    CuttingPlanner.prototype.renderStepCard = function () {
        var r = this.result, b = r && r.blocks[this.current], s = this.state.step, el = this.el, t = this.t.bind(this);
        if (!b || s < 1) {
            el.sc.className = 'co-sc'; el.sc.innerHTML = '';
            el.schint.style.display = b ? '' : 'none'; el.schint.textContent = t('step_hint');
            return;
        }
        var p = b.placed[s - 1], k = s - 1, ty = r.types[p.t];
        var dimTxt = [p.w, p.h, p.d].map(function (v) { return fmt(v, 1); }).join(' × ') + ' ' + t('u_cm');
        el.schint.style.display = 'none';
        el.sc.className = 'co-sc on';
        el.sc.innerHTML =
            '<figure dir="ltr">' + planSvg(b, k, r, 'face') + '<figcaption>' + esc(t('view_face')) + '</figcaption></figure>' +
            '<figure dir="ltr">' + planSvg(b, k, r, 'top') + '<figcaption>' + esc(t('view_top')) + '</figcaption></figure>' +
            '<div class="co-sc-info"><div class="co-sc-title"><span class="co-num">' + s + '</span>' + isoBox([p.w, p.h, p.d], ty.color, 46) +
            '<b>' + esc(ty.name) + '</b><small dir="ltr">' + dimTxt + '</small></div>' +
            '<div class="co-chips">' + this.chips(p) + '</div>' + this.axisKey() + '</div>';
    };

    CuttingPlanner.prototype.syncPlayer = function () {
        var r = this.result, b = r && r.blocks[this.current], el = this.el, N = b ? b.placed.length : 0;
        el.player.style.display = b ? '' : 'none';
        el.slider.max = N; el.slider.value = this.state.step;
        el.slabel.textContent = !b ? '' : this.state.step ? this.t('step_n', { k: this.state.step, n: N }) : this.t('step_overview');
        el.play.textContent = this._play ? '⏸' : '▶';
        el.play.title = this.t(this._play ? 'step_pause' : 'step_play');
        this.renderStepCard();
        if (b) this.markStep();
    };

    CuttingPlanner.prototype.setStep = function (n) {
        var r = this.result, b = r && r.blocks[this.current];
        if (!b) return;
        this.state.step = Math.max(0, Math.min(b.placed.length, n | 0));
        if (this.state.step >= b.placed.length) this.stopPlay();
        this.syncPlayer();
        this.buildScene();
    };

    CuttingPlanner.prototype.stopPlay = function () {
        if (this._timer) { clearInterval(this._timer); this._timer = null; }
        this._play = false;
        if (this.el && this.el.play) { this.el.play.textContent = '▶'; this.el.play.title = this.t('step_play'); }
    };

    CuttingPlanner.prototype.togglePlay = function () {
        var self = this, r = this.result, b = r && r.blocks[this.current];
        if (!b) return;
        if (this._play) { this.stopPlay(); return; }
        if (this.state.step >= b.placed.length) this.state.step = 0;
        this._play = true;
        this.el.play.textContent = '⏸';
        var tickStep = function () {
            if (!self.root.isConnected) { self.stopPlay(); return; }
            var rb = self.result && self.result.blocks[self.current];
            if (!rb) { self.stopPlay(); return; }
            self.state.step++;
            if (self.state.step >= rb.placed.length) { self.state.step = rb.placed.length; self.stopPlay(); }
            self.syncPlayer();
            self.buildScene();
        };
        tickStep();
        if (this._play) this._timer = setInterval(tickStep, b.placed.length > 60 ? 350 : 1100);
    };

    /* ---------------------------- 3D ---------------------------------- */

    CuttingPlanner.prototype.initViewer = function () {
        var self = this;
        if (!global.THREE) {
            this.el.canvas.innerHTML = '<div class="p-4 text-danger">Three.js n\'a pas pu être chargé.</div>';
            return;
        }
        var T = global.THREE;
        var w = this.el.canvas.clientWidth || 600, h = this.el.canvas.clientHeight || 480;
        this.scene = new T.Scene();
        this.camera = new T.PerspectiveCamera(40, w / h, 0.1, 10000);
        this.renderer = new T.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        this.renderer.setSize(w, h);
        this.el.canvas.appendChild(this.renderer.domElement);

        this.scene.add(new T.AmbientLight(0xffffff, 0.75));
        var dl = new T.DirectionalLight(0xffffff, 0.6); dl.position.set(1, 2, 1.5); this.scene.add(dl);
        var dl2 = new T.DirectionalLight(0xffffff, 0.25); dl2.position.set(-1.5, -1, -1); this.scene.add(dl2);

        if (T.OrbitControls) {
            this.controls = new T.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.autoRotateSpeed = 1.5;
        }
        this.group = new T.Group();
        this.scene.add(this.group);
        this.raycaster = new T.Raycaster();
        this.mouse = new T.Vector2();

        this.renderer.domElement.addEventListener('mousemove', function (e) { self.hover(e); });
        this.renderer.domElement.addEventListener('mouseleave', function () { self.el.tip.style.display = 'none'; });

        var resize = function () {
            var w2 = self.el.canvas.clientWidth, h2 = self.el.canvas.clientHeight;
            if (!w2 || !h2) return;
            self.camera.aspect = w2 / h2; self.camera.updateProjectionMatrix();
            self.renderer.setSize(w2, h2);
        };
        if (window.ResizeObserver) new ResizeObserver(resize).observe(this.el.canvas);
        else window.addEventListener('resize', resize);

        var loop = function () {
            if (!self.root.isConnected) return;
            requestAnimationFrame(loop);
            if (self.controls) self.controls.update();
            self.renderer.render(self.scene, self.camera);
        };
        loop();
    };

    CuttingPlanner.prototype.clearScene = function () {
        var g = this.group;
        while (g.children.length) {
            var o = g.children.pop();
            o.traverse(function (n) {
                if (n.geometry) n.geometry.dispose();
                if (n.material) n.material.dispose();
            });
        }
    };

    CuttingPlanner.prototype.buildScene = function () {
        if (!this.group) return;
        var T = global.THREE, r = this.result;
        this.clearScene();
        this.pickables = [];
        if (!r || !r.blocks.length) return;

        var b = r.blocks[this.current], st = this.state;
        var cx = b.dims[0] / 2, cy = b.dims[1] / 2, cz = b.dims[2] / 2;
        var spread = 1 + st.explode * 0.6;
        var clipY = b.dims[1] * st.clip / 100;
        var group = this.group;

        // contour du bloc
        var bg = new T.BoxGeometry(b.dims[0], b.dims[1], b.dims[2]);
        var outline = new T.LineSegments(new T.EdgesGeometry(bg), new T.LineBasicMaterial({ color: 0x475569 }));
        group.add(outline);
        var shell = new T.Mesh(bg, new T.MeshBasicMaterial({ color: 0x94a3b8, transparent: true, opacity: 0.07, depthWrite: false }));
        group.add(shell);

        function add(x, y, z, w, h, d, material, edgeColor, meta) {
            var geo = new T.BoxGeometry(w, h, d);
            var mesh = new T.Mesh(geo, material);
            var mx = x + w / 2 - cx, my = y + h / 2 - cy, mz = z + d / 2 - cz;
            mesh.position.set(mx * spread, my * spread, mz * spread);
            var edges = new T.LineSegments(new T.EdgesGeometry(geo), new T.LineBasicMaterial({ color: edgeColor }));
            mesh.add(edges);
            mesh.userData = meta || {};
            group.add(mesh);
            if (meta) self_pick.push(mesh);
        }
        var self_pick = this.pickables;

        // Barres de couleur sur les 3 arêtes du coin d'origine : Longueur / Hauteur / Profondeur.
        var th = Math.max.apply(null, b.dims) * 0.012;
        [0, 1, 2].forEach(function (a) {
            var sz = [th, th, th], ps = [-cx, -cy, -cz];
            sz[a] = b.dims[a]; ps[a] = 0;
            var bar = new T.Mesh(new T.BoxGeometry(sz[0], sz[1], sz[2]), new T.MeshBasicMaterial({ color: AXH[a] }));
            bar.position.set(ps[0], ps[1], ps[2]);
            group.add(bar);
        });
        var UC = this.t('u_cm'), self = this;
        this.el.axes.innerHTML = [0, 1, 2].map(function (a) {
            return '<div class="co-axchip"><i style="background:' + AXC[a] + '"></i>' + esc(self.t('side' + a)) + ' <span>' + fmt(b.dims[a], 1) + ' ' + UC + '</span></div>';
        }).join('');

        var step = st.step || 0, N = b.placed.length;
        b.placed.forEach(function (p, idx) {
            if (p.y > clipY + EPS) return;
            if (step > 0 && idx > step - 1) return;            // pas encore coupée
            var ty = r.types[p.t];
            var cur = step > 0 && idx === step - 1, done = step > 0 && idx < step - 1;
            var mat = new T.MeshLambertMaterial({
                color: ty.color, transparent: true, opacity: cur ? 1 : done ? 0.4 : (st.wire ? 0.12 : 0.94),
                emissive: new T.Color(cur ? ty.color : 0x000000), emissiveIntensity: cur ? 0.35 : 1
            });
            add(p.x, p.y, p.z, p.w, p.h, p.d, mat, cur ? 0xfacc15 : 0x0f172a, { kind: 'piece', name: ty.name, dims: [p.w, p.h, p.d] });
            if (cur) {
                // zone de travail (jaune) + plans de coupe colorés selon le sens
                add(p.box.p[0], p.box.p[1], p.box.p[2], p.box.s[0], p.box.s[1], p.box.s[2],
                    new T.MeshBasicMaterial({ color: 0xfacc15, transparent: true, opacity: 0.12, depthWrite: false }), 0xca8a04, null);
                var ct = Math.max.apply(null, b.dims) * 0.006;
                p.cuts.forEach(function (c) {
                    var sz = p.box.s.slice(), ps = p.box.p.slice();
                    sz[c.axis] = ct; ps[c.axis] = c.at - ct / 2;
                    add(ps[0], ps[1], ps[2], sz[0], sz[1], sz[2],
                        new T.MeshBasicMaterial({ color: AXH[c.axis], transparent: true, opacity: 0.55, depthWrite: false, side: T.DoubleSide }), AXH[c.axis], null);
                });
            }
        });

        if (st.showRemnants && (step === 0 || step >= N)) {
            b.remnants.forEach(function (rm) {
                if (rm.p[1] > clipY + EPS) return;
                var mat = new T.MeshBasicMaterial({ color: rm.recoverable ? 0x3b82f6 : 0xef4444, transparent: true, opacity: rm.recoverable ? 0.38 : 0.5, depthWrite: false });
                add(rm.p[0], rm.p[1], rm.p[2], rm.s[0], rm.s[1], rm.s[2], mat, rm.recoverable ? 0x1d4ed8 : 0xb91c1c,
                    { kind: 'remnant', recoverable: !!rm.recoverable, dims: rm.s });
            });
        }
    };

    CuttingPlanner.prototype.fitCamera = function () {
        if (!this.camera || !this.result || !this.result.blocks.length) return;
        var b = this.result.blocks[this.current];
        var m = Math.max.apply(null, b.dims);
        this.camera.position.set(m * 1.35, m * 1.0, m * 1.6);
        this.camera.near = m / 100; this.camera.far = m * 20; this.camera.updateProjectionMatrix();
        if (this.controls) { this.controls.target.set(0, 0, 0); this.controls.update(); }
    };

    CuttingPlanner.prototype.hover = function (e) {
        if (!this.renderer || !this.pickables || !this.pickables.length) return;
        var rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        this.raycaster.setFromCamera(this.mouse, this.camera);
        var hits = this.raycaster.intersectObjects(this.pickables, false);
        // on ignore les chutes translucides si une pièce est derrière
        var hit = hits.filter(function (h) { return h.object.userData.kind === 'piece'; })[0] || hits[0];
        var tip = this.el.tip;
        if (!hit) { tip.style.display = 'none'; return; }
        var u = hit.object.userData;
        var label = u.kind === 'piece' ? u.name : this.t(u.recoverable ? 'tip_rec' : 'tip_loss');
        tip.innerHTML = '<b>' + esc(label) + '</b><br>' + u.dims.map(function (v) { return fmt(v, 1); }).join(' × ') + ' ' + this.t('u_cm') + ' — ' + fmt(m3(vol(u.dims)), 4) + ' ' + this.t('u_m3');
        tip.style.display = 'block';
        tip.style.left = Math.min(e.clientX - rect.left + 14, rect.width - 190) + 'px';
        tip.style.top = (e.clientY - rect.top + 14) + 'px';
    };

    global.CuttingOptimizer = {
        mount: function (root, config) { var p = new CuttingPlanner(root, config); root._planner = p; return p; },
        solve: solve // exposé pour les tests
    };
})(typeof window !== 'undefined' ? window : globalThis);
