import axios from "axios";
import * as env from "../Env";
import * as storage from "./Storage";
import * as jwt from "./JwtExtractor";
import { formatDateToAtom } from "./../modules/Utilities";

axios.defaults.baseURL = env.REACT_APP_API_URL + "/v1";
axios.defaults.timeout = 40000;

class ServiceClient {
  constructor() {
    const accessToken = storage.getAccessToken();
    const authorization = accessToken ? "Bearer " + accessToken : null;

    this.accessToken = accessToken;
    this.cancelToken = axios.CancelToken.source();
    this.request = axios.create({
      headers: {
        "Content-Type": "application/vnd.api+json",
        Authorization: authorization,
      },
      cancelToken: this.cancelToken.token,
    });
  }

  cancel = (message) => {
    if (this.request) {
      this.cancelToken.cancel(message);
    }
  };

  createTeamAccount = (team, displayName, password, email) => {
    return this.request
      .post("/users", {
        data: {
          type: "user",
          attributes: {
            team: team,
            displayName: displayName,
            password: password,
            email: email,
          },
        },
      })
      .then((response) => {
        return this.extractAttributesFromResponse(response)[0];
      })
      .catch((err) => this.handleErrors(err));
  };

  login = (team, email, password) => {
    return this.request
      .post("/auth/token", {
        data: {
          type: "token",
          attributes: {
            team: team,
            email: email,
            password: password,
          },
        },
      })
      .then((response) => {
        const {
          access_token,
          refresh_token,
        } = this.extractAttributesFromResponse(response)[0];

        storage.setAccessToken(access_token);
        storage.setRefreshToken(refresh_token);

        const {
          sub,
          role,
          email,
          team,
          displayName,
          preferredWorkingHours,
        } = jwt.decodedToken(access_token);

        const user = {
          id: sub,
          team: team,
          displayName: displayName,
          email: email,
          role: role,
          preferredWorkingHours: preferredWorkingHours,
        };

        storage.storeUser(user);

        return user;
      })
      .catch((err) => this.handleErrors(err));
  };

  addUser = (team, displayName, password, email, role) => {
    return this.request
      .post("/teams/" + team + "/users", {
        data: {
          type: "user",
          attributes: {
            team: team,
            displayName: displayName,
            password: password,
            email: email,
            role: role,
          },
        },
      })
      .then((response) => {
        return this.extractAttributesFromResponse(response)[0];
      })
      .catch((err) => this.handleErrors(err));
  };

  deleteUser = (team, user) => {
    return this.request
      .delete("/teams/" + team + "/users/" + user)
      .then((response) => {
        return true;
      })
      .catch((err) => this.handleErrors(err));
  };

  updateUser = (
    team,
    user,
    displayName,
    email,
    role,
    preferredWorkingHours
  ) => {
    return this.request
      .put("/teams/" + team + "/users/" + user, {
        data: {
          type: "user",
          id: user,
          attributes: {
            displayName: displayName,
            email: email,
            role: role,
            team: team,
            preferredWorkingHours: Number(preferredWorkingHours),
          },
        },
      })
      .then((response) => {
        return this.extractAttributesFromResponse(response)[0];
      })
      .catch((err) => this.handleErrors(err));
  };

  getUsers = (team) => {
    return this.request
      .get("/teams/" + team + "/users")
      .then((response) => {
        return this.extractAttributesFromResponse(response);
      })
      .catch((err) => this.handleErrors(err));
  };

  getEntries = (team, user, before = null, after = null) => {
    let params = {};
    if (before) params["date_before"] = formatDateToAtom(before);
    if (after) params["date_after"] = formatDateToAtom(after);

    return this.request
      .get("/teams/" + team + "/users/" + user + "/entries", { params: params })
      .then((response) => {
        const entries = this.extractAttributesFromResponse(response).map(
          (entry) => ({
            ...entry,
            date: new Date(entry.date),
          })
        );

        return entries;
      })
      .catch((err) => this.handleErrors(err));
  };

  addEntry = (team, user, entry) => {
    return this.request
      .post("/teams/" + team + "/users/" + user + "/entries", {
        data: {
          type: "entry",
          attributes: {
            notes: entry.notes,
            date: formatDateToAtom(entry.date),
            hoursWorked: entry.hoursWorked,
          },
        },
      })
      .then((response) => {
        const e = this.extractAttributesFromResponse(response)[0];
        return { ...e, date: new Date(e.date) };
      })
      .catch((err) => this.handleErrors(err));
  };

  updateEntry = (team, user, entry) => {
    return this.request
      .put("/teams/" + team + "/users/" + user + "/entries/" + entry.id, {
        data: {
          type: "entry",
          attributes: {
            notes: entry.notes,
            date: formatDateToAtom(entry.date),
            hoursWorked: entry.hoursWorked,
          },
        },
      })
      .then((response) => {
        const e = this.extractAttributesFromResponse(response)[0];
        return { ...e, date: new Date(e.date) };
      })
      .catch((err) => this.handleErrors(err));
  };

  deleteEntry = (team, user, entry) => {
    return this.request
      .delete("/teams/" + team + "/users/" + user + "/entries/" + entry.id)
      .then((response) => {
        return true;
      })
      .catch((err) => this.handleErrors(err));
  };

  getDownloadUrl = (team, user, before, after) => {
    return (
      axios.defaults.baseURL +
      "/entries/download.php?team=" +
      team +
      "&user=" +
      user +
      "&date_before=" +
      formatDateToAtom(before) +
      "&date_after=" +
      formatDateToAtom(after) +
      "&token=" +
      this.accessToken
    );
  };

  changePassword = (team, user, password) => {
    return this.request
      .patch("/teams/" + team + "/users/" + user, {
        data: {
          type: "user",
          attributes: {
            password: password,
          },
        },
      })
      .then((response) => {
        return this.extractAttributesFromResponse(response)[0];
      })
      .catch((err) => this.handleErrors(err));
  };

  extractAttributesFromResponse = (response) => {
    const { data } = response.data;
    let modifiableData = data;

    if (!Array.isArray(modifiableData)) {
      modifiableData = [data];
    }

    const elements = modifiableData.map((el) => {
      return { id: el.id, ...el.attributes };
    });

    return elements;
  };

  handleErrors = (err) => {
    const { response } = err;
    let errors = [];

    if (!err.response) {
      errors = [err.message];
    } else {
      errors =
        response.status >= 400 && response.status < 500
          ? response.data.errors.map((err) => err.title)
          : ["Something went wrong. Try again!!"];
    }

    throw errors;
  };
}

export default ServiceClient;
