import { addEntryToReducer } from "./../../store/ReducerHelpers";

const userId = 10;

const newEntry = {
  note: "this is a dummy entry",
  date: new Date(),
  hoursWorked: 10,
};

const existingEntry = {
  note: "this is a non modified entry",
  date: new Date("2020-06-24"),
  hoursWorked: 5,
};

const existingAnotherUserEntries = {
  userId: 8,
  entries: [existingEntry],
};

test("Existing entry can only be updated without destroying existing state ", () => {
  const initialState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [
        existingEntry,
        {
          date: new Date(),
          note: "this is an old note",
          hoursWorked: 8,
        },
      ],
    },
  ];

  const expectedState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [existingEntry, newEntry],
    },
  ];

  const receivedState = addEntryToReducer(initialState, userId, newEntry);

  expect(receivedState).toStrictEqual(expectedState);
});

test("New entry can only be added without destroying existing state ", () => {
  const initialState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [existingEntry],
    },
  ];

  const expectedState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [existingEntry, newEntry],
    },
  ];

  const receivedState = addEntryToReducer(initialState, userId, newEntry);

  expect(receivedState).toStrictEqual(expectedState);
});

test("New entry can be added to an empty state", () => {
  const initialState = [];

  const expectedState = [
    {
      userId: userId,
      entries: [newEntry],
    },
  ];

  const receivedState = addEntryToReducer(initialState, userId, newEntry);

  expect(receivedState).toStrictEqual(expectedState);
});
