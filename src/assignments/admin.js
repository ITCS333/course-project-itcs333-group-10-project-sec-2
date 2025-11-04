/*
  Requirement: Make the "Manage Assignments" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="assignments-tbody"` to the <tbody> element
     so you can select it.
  
  3. Implement the TODOs below.
*/


// --- Global Data Store ---
// This will hold the assignments loaded from the JSON file.
let assignments = [];

// --- Element Selections ---
// TODO: Select the assignment form ('#assignment-form').
let assignmentForm = document.getElementById("assignment-form");
// TODO: Select the assignments table body ('#assignments-tbody').
let assignmentsTbody = document.getElementById("assignments-tbody");
// --- Functions ---

/**
 * TODO: Implement the createAssignmentRow function.
 * It takes one assignment object {id, title, dueDate}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `dueDate`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createAssignmentRow(assignment) {
  // ... your implementation here ...
  const { id, title, dueDate } = assignment;

  // Create table row
  const tr = document.createElement('tr');

  // Title cell
  const titleTd = document.createElement('td');
  titleTd.textContent = title;

  // Due date cell
  const dateTd = document.createElement('td');
  dateTd.textContent = dueDate;

  // Actions cell
  const actionsTd = document.createElement('td');

  // Edit button
  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-btn');
  editBtn.dataset.id = id; // sets data-id

  // Delete button
  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = id;

  // Append buttons to actions cell
  actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);

  // Append all cells to the row
  tr.appendChild(titleTd);
  tr.appendChild(dateTd);
  tr.appendChild(actionsTd);

  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `assignmentsTableBody`.
 * 2. Loop through the global `assignments` array.
 * 3. For each assignment, call `createAssignmentRow()`, and
 * append the resulting <tr> to `assignmentsTableBody`.
 */
function renderTable() {
  // ... your implementation here ...
  assignmentsTbody.innerHTML = '';

  // 2. Loop through global assignments array
  assignments.forEach(assignment => {
    // 3. Create a row for each assignment
    const row = createAssignmentRow(assignment);

    // Append row to tbody
    assignmentsTbody.appendChild(row);
  });
}

/**
 * TODO: Implement the handleAddAssignment function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, description, due date, and files inputs.
 * 3. Create a new assignment object with a unique ID (e.g., `id: \`asg_${Date.now()}\``).
 * 4. Add this new assignment object to the global `assignments` array (in-memory only).
 * 5. Call `renderTable()` to refresh the list.
 * 6. Reset the form.
 */
function handleAddAssignment(event) {
  // ... your implementation here ...
  // 1. Prevent page reload on submit
  event.preventDefault();

  // 2. Get form input values
  const title = document.getElementById("assignment-title").value;
  const description = document.getElementById("assignment-description").value;
  const dueDate = document.getElementById("assignment-due-date").value;
  const files = document.getElementById("assignment-files").value; // or .files if needed

  // 3. Create assignment object with unique ID
  const newAssignment = {
    id: `asg_${Date.now()}`,
    title: title,
    description: description,
    dueDate: dueDate,
    files: files
  };

  // 4. Add to global assignments array
  assignments.push(newAssignment);

  // 5. Refresh the table
  renderTable();

  // 6. Reset form
  event.target.reset();
}

/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `assignmentsTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `assignments` array by filtering out the assignment
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // ... your implementation here ...
  // 1. Check if a delete button was clicked
  if (event.target.classList.contains("delete-btn")) {
    
    // 2. Get the ID from data-id
    const idToDelete = event.target.dataset.id;

    // 3. Remove this assignment from the global array
    assignments = assignments.filter(assignment => assignment.id !== idToDelete);

    // 4. Re-render the table
    renderTable();
  }
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response and store the result in the global `assignments` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `assignmentForm` (calls `handleAddAssignment`).
 * 5. Add the 'click' event listener to `assignmentsTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  // ... your implementation here ...
  
  
  try {
    // 1. Fetch assignments.json
    const response = await fetch('api/assignments.json');
    if (!response.ok) {
            throw new Error("Error");
        }

    // 2. Parse JSON and store in global array
    const data = await response.json();
   assignments.push(...data);// overwrite global

    // 3. Render table initially
    renderTable();

    // 4. Add submit listener to the form
    assignmentForm.addEventListener('submit', handleAddAssignment);

    // 5. Add click listener to table body (for edit/delete)
    assignmentsTbody.addEventListener('click', handleTableClick);

  } catch (error) {
    console.error('Error loading assignments:', error);
  }
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
