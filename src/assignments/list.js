/*
  Requirement: Populate the "Course Assignments" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="assignment-list-section"` to the
     <section> element that will contain the assignment articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the assignment list ('#assignment-list-section').
 let assignmentListSection = document.getElementById("assignment-list-section");
// --- Functions ---

/**
 * TODO: Implement the createAssignmentArticle function.
 * It takes one assignment object {id, title, dueDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * The "View Details" link's `href` MUST be set to `details.html?id=${id}`.
 * This is how the detail page will know which assignment to load.
 */
function createAssignmentArticle(assignment) {
  // ... your implementation here ...
   const { id, title, dueDate, description } = assignment;

  // Create elements
  const article = document.createElement("article");
  const h2 = document.createElement("h2");
  const dueP = document.createElement("p");
  const descP = document.createElement("p");
  const link = document.createElement("a");

  // Fill content
  h2.textContent = title;
  dueP.textContent = "Due: " + dueDate;
  descP.textContent = description;

  // Must link to details.html with ?id=xxx
  link.href = `details.html?id=${id}`;
  link.textContent = "View Details & Discussion";

  // Build structure
  article.appendChild(h2);
  article.appendChild(dueP);
  article.appendChild(descP);
  article.appendChild(link);

  return article;
}

/**
 * TODO: Implement the loadAssignments function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the assignments array. For each assignment:
 * - Call `createAssignmentArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadAssignments() {
  // ... your implementation here ...

   try {
    // 1. Fetch JSON data
    const response = await fetch("assignments.json");

    // 2. Parse data
    const assignments = await response.json();

    // 3. Clear old content
    assignmentListSection.innerHTML = "";

    // 4. Loop and render each assignment
    assignments.forEach(assignment => {
      const article = createAssignmentArticle(assignment);
      assignmentListSection.appendChild(article);
    });

  } catch (error) {
    assignmentListSection.innerHTML = "<h1>Error loading assignments.</h1>";
    console.error(error);
  }
}

// --- Initial Page Load ---
// Call the function to populate the page.
loadAssignments();
