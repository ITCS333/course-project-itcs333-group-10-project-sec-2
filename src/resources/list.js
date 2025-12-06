/*
  Requirement: Populate the "Course Resources" list page.
*/

// --- Element Selections ---
const listSection = document.querySelector('#resource-list-section');

// --- Functions ---

/**
 * Creates an <article> element for a single resource
 */
function createResourceArticle(resource) {
  const article = document.createElement('article');

  // Resource title
  const title = document.createElement('h3');
  title.textContent = resource.title;
  article.appendChild(title);

  // Resource description
  const desc = document.createElement('p');
  desc.textContent = resource.description;
  article.appendChild(desc);

  // Link to details page
  const link = document.createElement('a');
  link.href = `details.html?id=${resource.id}`;
  link.textContent = 'View Resource & Discussion';
  article.appendChild(link);

  return article;
}

/**
 * Loads resources from JSON and populates the list
 */
async function loadResources() {
  try {
    const response = await fetch('resources.json'); // fetch resource list
    if (!response.ok) throw new Error('Failed to load resources');

    const resources = await response.json();
    listSection.innerHTML = ''; // clear existing content

    resources.forEach(resource => {
      const article = createResourceArticle(resource);
      listSection.appendChild(article);
    });
  } catch (error) {
    console.error(error);
    listSection.innerHTML = '<p>Error loading resources.</p>';
  }
}

// --- Initial Page Load ---
loadResources();
