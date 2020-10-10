import { removeEntryFromReducer } from "../../store/ReducerHelpers";

const userId = 10;

const existingEntry = {
  note: "Existing entry",
  date: new Date("2020-06-24"),
  hoursWorked: 5,
};

const anotherExistingEntry = {
  note: "Another existing entry",
  date: new Date("2020-06-26"),
  hoursWorked: 5,
};

const existingAnotherUserEntries = {
  userId: 8,
  entries: [existingEntry],
};

test("Entry can be deleted without destroying existing state", () => {
  const initialState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [existingEntry, anotherExistingEntry],
    },
  ];

  const expectedState = [
    existingAnotherUserEntries,
    {
      userId: userId,
      entries: [existingEntry],
    },
  ];

  const receivedState = removeEntryFromReducer(
    initialState,
    userId,
    anotherExistingEntry
  );

  expect(receivedState).toStrictEqual(expectedState);
});

test("Entry being removed from empty state wont crash", () => {
  const expectedState = [
    {
      userId: userId,
      entries: [],
    },
  ];

  const initialState = [];

  const receivedState = removeEntryFromReducer(
    initialState,
    userId,
    anotherExistingEntry
  );

  expect(receivedState).toStrictEqual(expectedState);
});
