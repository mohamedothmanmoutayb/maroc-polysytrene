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
                ['Détail de découpe', '« Ce que contient ce bloc » liste les pièces et leur orientation. « Chutes à récupérer » liste les plus gros restes. « Ordre des coupes » donne, pièce par pièce, où couper (en cm depuis le bord du bloc).']
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
                ['تفاصيل القصّ', '«ما تحتويه هذه الكتلة» يعرض القطع واتجاهها. «القصاصات الواجب استرجاعها» يعرض أكبر البقايا. «ترتيب القصّ» يحدد لكل قطعة أين يجب القصّ (بالسنتيمتر من حافة الكتلة).']
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

    var CSS = '' +
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
        this.state = { explode: 0, clip: 100, showRemnants: true, wire: false, rotate: false };
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
            '<div style="position:relative"><div class="co-canvas"></div><div class="co-tip"></div><div class="co-busy"></div></div>' +
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
            '<div class="co-panel co-why mt-3" style="display:none"><h6 class="co-why-t"></h6><div class="co-body"><ul class="co-why-b"></ul></div></div>' +
            '<div class="co-panel mt-3"><h6>' + i('detail_title') + '</h6><div class="co-body co-detail"></div></div></div>';

        var q = function (s) { return self.root.querySelector(s); };
        this.el = {
            canvas: q('.co-canvas'), tip: q('.co-tip'), busy: q('.co-busy'), legend: q('.co-legend'),
            kpis: q('.co-kpis'), warnings: q('.co-warnings'), blocks: q('.co-blocks tbody'), detail: q('.co-detail'),
            select: q('.co-block-select'), why: q('.co-why'), whyT: q('.co-why-t'), whyB: q('.co-why-b'), help: q('.co-helpbox'), wrap: q('.co-wrap')
        };

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
            el.why.style.display = 'none';
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
                '<td><div class="co-bar"><i style="width:' + eff.toFixed(1) + '%"></i></div><small>' + fmt(eff, 1) + ' %</small></td>' +
                '<td class="text-end">' + fmt(m3(vol(b.dims) - b.used), 3) + ' ' + UM + '</td></tr>';
        }).join('');

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
        this.el.select.value = this.current;
        var rows = this.el.blocks.querySelectorAll('tr');
        rows.forEach(function (tr, k) { tr.classList.toggle('active', k === this.current); }, this);
        this.renderDetail();
        this.buildScene();
        if (!keep) this.fitCamera();
    };

    CuttingPlanner.prototype.renderDetail = function () {
        var self = this, r = this.result, b = r.blocks[this.current], t = function (k, p) { return self.t(k, p); };
        var UC = t('u_cm'), UM = t('u_m3');
        var cm = function (v) { return fmt(v, 1) + ' ' + UC; };
        var dimTxt = function (d) { return d.map(function (v) { return fmt(v, 1); }).join(' × ') + ' ' + UC; };
        var sw = function (ti) { return '<i class="co-sw" style="background:' + r.types[ti].color + '"></i>'; };
        var ltr = function (s) { return '<span dir="ltr">' + s + '</span>'; };

        var groups = {};
        b.placed.forEach(function (p) {
            var key = p.t + '|' + p.w + 'x' + p.h + 'x' + p.d;
            groups[key] = groups[key] || { t: p.t, dims: [p.w, p.h, p.d], n: 0 };
            groups[key].n++;
        });
        var summary = Object.keys(groups).map(function (k) {
            var g = groups[k];
            return '<li class="co-line"><b>' + t('d_piece_n', { n: g.n }) + '</b> ' + sw(g.t) + esc(r.types[g.t].name) +
                '<div class="co-sub">' + t('d_cut_with') + ' : ' + t('side0') + ' <b>' + ltr(cm(g.dims[0])) + '</b> · ' + t('side1') + ' <b>' + ltr(cm(g.dims[1])) +
                '</b> · ' + t('side2') + ' <b>' + ltr(cm(g.dims[2])) + '</b></div></li>';
        }).join('');

        var recover = b.remnants.filter(function (x) { return x.recoverable; }).sort(function (x, y) { return vol(y.s) - vol(x.s); });
        var remHtml = recover.slice(0, 8).map(function (x) {
            return '<li class="co-line">' + ltr(dimTxt(x.s)) + ' <span class="co-sub" style="display:inline">(' + fmt(m3(vol(x.s)), 3) + ' ' + UM + ')</span></li>';
        }).join('');

        var steps = b.placed.slice(0, 80).map(function (p, n) {
            var cuts = p.cuts.map(function (c, k) {
                return '<div class="co-cut">' + t('d_cut', { k: k + 1, at: ltr(cm(c.at)), side: t('side' + c.axis) }) + '</div>';
            }).join('') || '<div class="co-cut">' + t('d_nocut') + '</div>';
            return '<li class="co-step"><div class="co-step-h"><span class="co-num">' + (n + 1) + '</span>' + sw(p.t) +
                '<b>' + esc(r.types[p.t].name) + '</b> <span class="co-sub" style="display:inline">(' + ltr(dimTxt([p.w, p.h, p.d])) + ')</span></div>' +
                '<div class="co-sub">' + t('d_zone') + ' : ' + ltr(dimTxt(p.box.s)) + ' — ' + t('d_start') + ' ' + ltr(cm(p.box.p[0])) + ' ' + t('unit_len') + ', ' +
                ltr(cm(p.box.p[1])) + ' ' + t('unit_h') + ', ' + ltr(cm(p.box.p[2])) + ' ' + t('unit_d') + '</div>' + cuts + '</li>';
        }).join('');

        this.el.detail.innerHTML =
            '<div class="row"><div class="col-lg-5">' +
            '<div class="co-h">' + t('d_contains') + '</div><ul class="co-list">' + summary + '</ul>' +
            '<div class="co-h">' + t('d_rec_title') + '</div><ul class="co-list">' + (remHtml || '<li class="co-line">' + t('d_rec_none') + '</li>') + '</ul></div>' +
            '<div class="col-lg-7"><div class="co-h">' + t('d_steps') + '</div>' +
            '<ol class="co-steps">' + steps + (b.placed.length > 80 ? '<li class="co-line">' + t('d_more', { n: b.placed.length - 80 }) + '</li>' : '') + '</ol>' +
            '<div class="co-help">' + t('d_read') + '</div></div></div>';
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
            mesh.userData = meta;
            group.add(mesh);
            if (meta) self_pick.push(mesh);
        }
        var self_pick = this.pickables;

        b.placed.forEach(function (p) {
            if (p.y > clipY + EPS) return;
            var ty = r.types[p.t];
            var mat = new T.MeshLambertMaterial({
                color: ty.color, transparent: true, opacity: st.wire ? 0.12 : 0.94, wireframe: false
            });
            add(p.x, p.y, p.z, p.w, p.h, p.d, mat, 0x0f172a, { kind: 'piece', name: ty.name, dims: [p.w, p.h, p.d] });
        });

        if (st.showRemnants) {
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
