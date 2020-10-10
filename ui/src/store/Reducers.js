import { combineReducers } from "redux";
import * as actionTypes from "./ActionTypes";
import * as helpers from "./ReducerHelpers";

const initialAuthState = { successMessage: null, errorMessage: null };

const auth_reducer = (state = initialAuthState, action) => {
  switch (action.type) {
    case actionTypes.SET_ERROR_MESSAGE:
      return {
        ...state,
        errorMessage: action.payload.message,
      };
    case actionTypes.SET_SUCCESS_MESSAGE:
      return {
        ...state,
        successMessage: action.payload.message,
      };
    default:
      return state;
  }
};

const initialUsersState = {
  isLoaded: false,
  list: [],
};

const users_reducer = (state = initialUsersState, action) => {
  switch (action.type) {
    case actionTypes.ADD_USER_TO_LIST:
      return {
        isLoaded: state.isLoaded,
        list: [...state.list, action.payload.user],
      };
    case actionTypes.REMOVE_USER_FROM_LIST:
      return {
        isLoaded: state.isLoaded,
        list: state.list.filter((u) => u.id !== action.payload.user.id),
      };
    case actionTypes.UPDATE_USER_IN_LIST:
      let newUsersList = state.list.filter(
        (u) => u.id !== action.payload.user.id
      );
      newUsersList = [...newUsersList, action.payload.user];
      return {
        isLoaded: state.isLoaded,
        list: newUsersList,
      };
    case actionTypes.LOAD_USERS:
      return {
        isLoaded: true,
        list: [...action.payload.users],
      };
    default:
      return state;
  }
};

const initialUserState = {
  isLoaded: false,
  currentUser: null,
};

const user_reducer = (state = initialUserState, action) => {
  switch (action.type) {
    case actionTypes.SET_USER:
      return {
        isLoaded: action.payload.isLoaded,
        currentUser: action.payload.currentUser,
      };
    default:
      return state;
  }
};

const initialEntriesState = [];

const entries_reducer = (state = initialEntriesState, action) => {
  switch (action.type) {
    case actionTypes.LOAD_ENTRIES:
      let newEntriesList = state.filter(
        (e) => e.userId !== action.payload.entries.userId
      );
      return [...newEntriesList, action.payload.entries];
    case actionTypes.ADD_ENTRY:
      return helpers.addEntryToReducer(
        state,
        action.payload.userId,
        action.payload.entry
      );
    case actionTypes.DELETE_ENTRY:
      return helpers.removeEntryFromReducer(
        state,
        action.payload.userId,
        action.payload.entry
      );
    default:
      return state;
  }
};

const appReducer = combineReducers({
  user: user_reducer,
  users: users_reducer,
  entries: entries_reducer,
  auth: auth_reducer,
});

const rootReducer = (state, action) => {
  if (action.type === actionTypes.LOG_OUT) {
    state = undefined;
  }

  return appReducer(state, action);
};

export default rootReducer;
