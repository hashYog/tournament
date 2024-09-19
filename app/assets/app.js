import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
let currentStage = '';

function fetchTournament() {
    fetch('/api/tournament')
        .then(response => response.json())
        .then(data => {
            currentStage = data.stage;
            updateTournamentView(data);
        })
        .catch(error => console.error('Error fetching tournament data:', error));
}

function updateTournamentView(data) {
    document.getElementById('divisions').innerHTML = '';
    document.querySelector('.playoffGrid').innerHTML = '';

    data.divisions.forEach(division => {
        createDivisionTable(division.name, division.teams);
        if (currentStage !== 'before') {
            fillDivisionResults(`division-${division.name.toLowerCase()}`, division.matches);
        }
    });

    if (currentStage !== 'before' && currentStage !== 'group') {
        renderPlayoffGrid(data.playoffMatches);
    }

    const nextStageBtn = document.getElementById('nextStageBtn');
    const currentStageIndex = stages.indexOf(currentStage);
}

function createDivisionTable(division, teams) {
    const divisionElement = document.createElement('div');
    divisionElement.id = `division-${division.toLowerCase()}`;
    divisionElement.innerHTML = `
        <h2>Division ${division}</h2>
        <table class="divisionTable">
            <tr>
                <th></th>
                ${teams.map(team => `<th><div class="rotateText teamName">${team.name}</div></th>`).join('')}
                <th>Points</th>
            </tr>
            ${teams.map(rowTeam => `
                <tr>
                    <th class="teamName">${rowTeam.name}</th>
                    ${teams.map(colTeam =>
        rowTeam.id === colTeam.id
            ? '<td class="emptyCell">-</td>'
            : `<td data-first="${rowTeam.id}" data-second="${colTeam.id}"></td>`
    ).join('')}
                    <td class="teamPoints">${rowTeam.points}</td>
                </tr>
            `).join('')}
        </table>
    `;
    document.getElementById('divisions').appendChild(divisionElement);
}

function fillDivisionResults(divisionId, matches) {
    const table = document.querySelector(`#${divisionId} .divisionTable`);
    const cells = table.querySelectorAll('td[data-first]');

    cells.forEach(cell => {
        const firstId = cell.dataset.first;
        const secondId = cell.dataset.second;
        const match = matches.find(m =>
            (m.firstTeamId == firstId && m.secondTeamId == secondId) ||
            (m.secondTeamId == firstId && m.firstTeamId == secondId)
        );

        if (match) {
            let score;
            if (match.firstTeamId == firstId) {
                score = `${match.firstScore}:${match.secondScore}`;
            } else {
                score = `${match.secondScore}:${match.firstScore}`;
            }
            cell.innerHTML = `<div class="matchResult">${score}</div>`;
        }
    });

    // // Update team points
    // const rows = table.querySelectorAll('tr');
    // rows.forEach((row, index) => {
    //     if (index > 0) { // Skip header row
    //         const teamId = row.querySelector('td[data-first]').dataset.first;
    //         const team = matches[0].firstTeamId == teamId ? matches[0].firstTeam : matches[0].secondTeam;
    //         row.querySelector('.teamPoints').textContent = team.points;
    //     }
    // });
}

function renderPlayoffGrid(matches) {
    const playoffGrid = document.querySelector('.playoffGrid');
    playoffGrid.innerHTML = '';

    const stageMatches = {};
    matches.forEach(match => {
        if (!stageMatches[match.stage]) {
            stageMatches[match.stage] = [];
        }
        stageMatches[match.stage].push(match);
    });

    Object.entries(stageMatches).forEach(([stage, matches]) => {
        const stageElement = document.createElement('div');
        stageElement.className = 'playoffStage';
        stageElement.innerHTML = `<h3>${stage}</h3>`;

        matches.forEach(match => {
            const matchElement = document.createElement('div');
            matchElement.className = 'playoffMatch';
            matchElement.innerHTML = `
                <div class="teamName">${match.firstTeamName}</div>
                <div class="matchResult">${match.firstScore} - ${match.secondScore}</div>
                <div class="teamName">${match.secondTeamName}</div>
            `;
            stageElement.appendChild(matchElement);
        });

        playoffGrid.appendChild(stageElement);
    });
}

document.getElementById('nextStageBtn').addEventListener('click', () => {
    fetch('/api/tournament/generate', { method: 'POST' })
        .then(data => {
            fetchTournament();
        })
        .catch(error => console.error('Error generating next stage:', error));
});

document.getElementById('resetBtn').addEventListener('click', () => {
    fetch('/api/tournament/reset', { method: 'POST' })
        .then(data => {
            fetchTournament();
        })
        .catch(error => console.error('Error reset:', error));
});

fetchTournament();