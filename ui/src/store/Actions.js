import * as actionTypes from "./ActionTypes";

export const setUser = (user) => {
  return {
    type: actionTypes.SET_USER,
    payload: {
      isLoaded: true,
      currentUser: user,
    },
  };
};

export const setSuccessMessage = (message) => {
  return {
    type: actionTypes.SET_SUCCESS_MESSAGE,
    payload: {
      message: message,
    },
  };
};

export const setErrorMessage = (message) => {
  return {
    type: actionTypes.SET_ERROR_MESSAGE,
    payload: {
      message: message,
    },
  };
};

export const addUserToList = (user) => {
  return {
    type: actionTypes.ADD_USER_TO_LIST,
    payload: {
      user: user,
    },
  };
};

export const removeUserFromList = (user) => {
  return {
    type: actionTypes.REMOVE_USER_FROM_LIST,
    payload: {
      user: user,
    },
  };
};

export const updateUserInList = (user) => {
  return {
    type: actionTypes.UPDATE_USER_IN_LIST,
    payload: {
      user: user,
    },
  };
};

export const loadUsers = (users) => {
  return {
    type: actionTypes.LOAD_USERS,
    payload: {
      users: users,
    },
  };
};

export const loadEntries = (userId, entries) => {
  return {
    type: actionTypes.LOAD_ENTRIES,
    payload: {
      entries: {
        userId: userId,
        lastLoaded: Date.now(),
        entries: entries,
      },
    },
  };
};

export const addEntryToList = (userId, entry) => {
  return {
    type: actionTypes.ADD_ENTRY,
    payload: {
      userId: userId,
      entry: entry,
    },
  };
};

export const deleteEntryFromList = (userId, entry) => {
  return {
    type: actionTypes.DELETE_ENTRY,
    payload: {
      userId: userId,
      entry: entry,
    },
  };
};

export const logOut = () => {
  return {
    type: actionTypes.LOG_OUT,
    payload: {
      users: [],
    },
  };
};
