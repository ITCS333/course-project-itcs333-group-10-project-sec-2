/* 
  Requirement: Populate the "Weekly Course Breakdown" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="week-list-section"` to the
     <section> element that will contain the weekly articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the week list ('#week-list-section').
const listSection = document.querySelector('#week-list-section');

// --- Functions ---

/**
 * TODO: Implement the createWeekArticle function.
 * It takes one week object {id, title, startDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * - The "View Details & Discussion" link's `href` MUST be set to `details.html?id=${id}`.
 * (This is how the detail page will know which week to load).
 */
function createWeekArticle(week) {
    const article = document.createElement('article');

    const h2 = document.createElement('h2');
    h2.textContent = week.title;
    article.appendChild(h2);

    const pStart = document.createElement('p');
    pStart.textContent = `Starts on: ${week.startDate}`;
    article.appendChild(pStart);

    const pDesc = document.createElement('p');
    pDesc.textContent = week.description;
    article.appendChild(pDesc);

    const a = document.createElement('a');
    a.href = `details.html?id=${encodeURIComponent(week.id)}`;
    a.textContent = 'View Details & Discussion';
    article.appendChild(a);

    return article;
}

/**
 * TODO: Implement the loadWeeks function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the weeks array. For each week:
 * - Call `createWeekArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadWeeks() {
    try {
        const response = await fetch('api/weeks.json');
        if (!response.ok) throw new Error('Failed to load weeks.json');
        const weeks = await response.json();

        listSection.innerHTML = '';

        if (!Array.isArray(weeks) || weeks.length === 0) {
            listSection.innerHTML = '<p>No weeks available.</p>';
            return;
        }

        weeks.forEach(week => {
            const article = createWeekArticle(week);
            listSection.appendChild(article);
        });
    } catch (error) {
        console.error('Error loading weeks:', error);
        if (listSection) listSection.innerHTML = '<p class="error">Error loading weeks.</p>';
    }
}

// --- Initial Page Load ---
// Call the function to populate the page.
loadWeeks();
