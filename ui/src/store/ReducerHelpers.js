import { formatDateToAtom } from "./../modules/Utilities";

export const addEntryToReducer = (state, userId, entry) => {
  const entryDate = entry.date;
  const filteredEntries = state.filter((e) => e.userId === userId);
  const userEntries =
    filteredEntries.length > 0 ? filteredEntries[0].entries : [];

  const existingEntryCopy = userEntries.filter(
    (e) => formatDateToAtom(e.date) === formatDateToAtom(entryDate)
  );

  const modifiedUserEntries =
    filteredEntries.length > 0
      ? existingEntryCopy.length > 0
        ? userEntries.map((e) => {
            return formatDateToAtom(e.date) === formatDateToAtom(entryDate)
              ? { ...e, ...entry }
              : e;
          })
        : [...userEntries, entry]
      : [entry];

  const filteredStateWithoutUserEntries = state.filter(
    (e) => e.userId !== userId
  );

  const newState = [
    ...filteredStateWithoutUserEntries,
    { userId: userId, entries: modifiedUserEntries },
  ];
  return newState;
};

export const removeEntryFromReducer = (state, userId, entry) => {
  const entryDate = entry.date;
  const filteredEntries = state.filter((e) => e.userId === userId);
  const userEntries =
    filteredEntries.length > 0 ? filteredEntries[0].entries : [];
  const filteredUserEntries = userEntries.filter(
    (e) => formatDateToAtom(e.date) !== formatDateToAtom(entryDate)
  );
  const filteredStateWithoutUserEntries = state.filter(
    (e) => e.userId !== userId
  );

  const newState = [
    ...filteredStateWithoutUserEntries,
    { userId: userId, entries: [...filteredUserEntries] },
  ];
  return newState;
};
